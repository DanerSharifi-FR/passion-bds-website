<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allo;
use App\Models\AlloUsage;
use App\Services\AlloService;
use App\Services\AlloSlotService;
use App\Services\AlloUsageService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AlloApiController extends Controller
{
    private const ADMIN_ROLES = [
        'ROLE_SUPER_ADMIN',
        'ROLE_BLOGGER',
        'ROLE_GAMEMASTER',
        'ROLE_SHOP',
        'ROLE_TEAM',
    ];

    public function index()
    {
        $allos = Allo::query()
            ->with(['admins:id,display_name,university_email'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Allo $allo): array => $this->formatAllo($allo));

        return response()->json(['data' => $allos]);
    }

    public function listAdmins()
    {
        $admins = DB::table('users as u')
            ->join('user_roles as ur', 'ur.user_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'ur.role_id')
            ->whereIn('r.name', self::ADMIN_ROLES)
            ->groupBy('u.id', 'u.display_name', 'u.university_email')
            ->select([
                'u.id',
                DB::raw('COALESCE(u.display_name, u.university_email) as name'),
                'u.university_email as email',
            ])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $admins]);
    }

    public function store(Request $request, AlloService $alloService, AlloSlotService $slotService)
    {
        $validated = $this->validateAllo($request);

        $allo = $alloService->createAllo($validated, $request->user());
        $slotService->generateSlotsForAllo($allo);
        $allo->load(['admins:id,display_name,university_email']);

        return response()->json(['data' => $this->formatAllo($allo)], 201);
    }

    public function update(Request $request, Allo $allo, AlloService $alloService, AlloSlotService $slotService)
    {
        $validated = $this->validateAllo($request);

        $allo = $alloService->updateAllo($allo, $validated, $request->user());
        $slotService->generateSlotsForAllo($allo);
        $allo->load(['admins:id,display_name,university_email']);

        return response()->json(['data' => $this->formatAllo($allo)]);
    }

    public function destroy(Allo $allo)
    {
        $allo->delete();

        return response()->json(['data' => 'ok']);
    }

    public function usages(Request $request)
    {
        $status = strtoupper(trim((string) $request->query('status', 'ACTIVE')));
        $alloId = $request->query('allo_id');
        $search = trim((string) $request->query('user', ''));

        $query = DB::table('allo_usages as au')
            ->join('allos as a', 'a.id', '=', 'au.allo_id')
            ->join('users as u', 'u.id', '=', 'au.user_id')
            ->leftJoin('users as h', 'h.id', '=', 'au.handled_by_id')
            ->leftJoin('users as d', 'd.id', '=', 'au.done_by_id')
            ->select([
                'au.id',
                'au.allo_id',
                'a.title as allo_title',
                'au.slot_start_at',
                'au.status',
                'au.created_at',
                'au.accepted_at',
                'au.done_at',
                'au.cancelled_at',
                'au.user_note',
                'au.handled_by_id',
                'u.id as user_id',
                'u.university_email as user_email',
                DB::raw('COALESCE(u.display_name, u.university_email) as user_name'),
                DB::raw('COALESCE(h.display_name, h.university_email) as handled_by_name'),
                DB::raw('COALESCE(d.display_name, d.university_email) as done_by_name'),
            ]);

        if ($status === 'ACTIVE') {
            $query->whereIn('au.status', [AlloUsageService::STATUS_PENDING, AlloUsageService::STATUS_ACCEPTED]);
        } elseif ($status !== 'ALL') {
            $query->where('au.status', $status);
        }

        if ($alloId !== null && $alloId !== '') {
            $query->where('au.allo_id', (int) $alloId);
        }

        if ($search !== '') {
            $query->where(function ($sub) use ($search) {
                $sub->where('u.display_name', 'like', "%{$search}%")
                    ->orWhere('u.university_email', 'like', "%{$search}%");
            });
        }

        $requests = $query
            ->orderByDesc('au.created_at')
            ->get()
            ->map(fn ($row): array => $this->formatUsageRow($row));

        return response()->json(['data' => $requests]);
    }

    public function visits(Request $request)
    {
        $mode = strtolower(trim((string) $request->query('mode', 'hours')));
        $maxHours = 168;
        $maxDays = 365;

        if (!in_array($mode, ['hours', 'days', 'range'], true)) {
            return response()->json(['message' => 'Mode de fenêtre invalide.'], 422);
        }

        if ($mode === 'hours' || $mode === 'days') {
            $rawN = $request->query('n', 24);
            if (!is_numeric($rawN)) {
                return response()->json(['message' => 'Valeur numérique invalide pour la fenêtre.'], 422);
            }
            $n = (int) $rawN;
            if ($n < 1) {
                return response()->json(['message' => 'La fenêtre doit être supérieure ou égale à 1.'], 422);
            }

            if ($mode === 'hours') {
                $n = min($n, $maxHours);
                $start = Carbon::now()->subHours($n);
            } else {
                $n = min($n, $maxDays);
                $start = Carbon::now()->subDays($n);
            }

            $end = Carbon::now();
            $counts = $this->fetchVisitsCounts($start, $end);

            return response()->json([
                'data' => $counts,
                'meta' => [
                    'mode' => $mode,
                    'n' => $n,
                    'from' => $start->toDateTimeString(),
                    'to' => $end->toDateTimeString(),
                ],
            ]);
        }

        $from = $request->query('from');
        $to = $request->query('to');

        if (!$from || !$to) {
            return response()->json(['message' => 'Les dates de début et de fin sont requises.'], 422);
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $from)->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $to)->endOfDay();
        } catch (\Throwable $exception) {
            return response()->json(['message' => 'Format de date invalide.'], 422);
        }

        if ($start->greaterThan($end)) {
            return response()->json(['message' => 'La date de début doit précéder la date de fin.'], 422);
        }

        $counts = $this->fetchVisitsCounts($start, $end);

        return response()->json([
            'data' => $counts,
            'meta' => [
                'mode' => $mode,
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
        ]);
    }

    public function updateUsage(Request $request, AlloUsage $usage, AlloUsageService $usageService)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                AlloUsageService::STATUS_PENDING,
                AlloUsageService::STATUS_ACCEPTED,
                AlloUsageService::STATUS_DONE,
                AlloUsageService::STATUS_CANCELLED,
            ])],
            'handled_by_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ]);

        $hasStatus = array_key_exists('status', $validated);
        $hasHandler = array_key_exists('handled_by_id', $validated);

        if (!$hasStatus && !$hasHandler) {
            return response()->json(['message' => 'Aucune modification demandée.'], 422);
        }

        $actor = $request->user();
        $isSuperAdmin = $actor?->hasRole('ROLE_SUPER_ADMIN') ?? false;

        if ($usage->status === AlloUsageService::STATUS_DONE && !$isSuperAdmin) {
            return response()->json(['message' => 'Seul le SUPER ADMIN peut modifier un allo réalisé.'], 403);
        }

        if ($hasHandler && $validated['handled_by_id'] !== null) {
            $this->ensureHandlerIsAllowed($usage, (int) $validated['handled_by_id']);
        }

        if ($hasStatus && $validated['status'] !== null) {
            $status = $validated['status'];

            if ($hasHandler && $validated['handled_by_id'] !== null && $status !== AlloUsageService::STATUS_ACCEPTED) {
                return response()->json(['message' => 'L’attribution doit être associée au statut "accepté".'], 422);
            }

            if ($status === AlloUsageService::STATUS_PENDING) {
                $this->resetUsageToPending($usage);
            } elseif ($status === AlloUsageService::STATUS_ACCEPTED) {
                if ($hasHandler && $validated['handled_by_id'] !== null) {
                    $this->assignUsage($usage, (int) $validated['handled_by_id']);
                } else {
                    $usageService->accept($usage, $actor);
                }
            } elseif ($status === AlloUsageService::STATUS_DONE) {
                $usageService->markDone($usage, $actor);
            } elseif ($status === AlloUsageService::STATUS_CANCELLED) {
                $usageService->cancel($usage, $actor);
            }
        } elseif ($hasHandler) {
            if ($validated['handled_by_id'] === null) {
                $this->resetUsageToPending($usage);
                $usage->load(['allo', 'user', 'handledBy', 'doneBy']);

                return response()->json(['data' => $this->formatUsage($usage)]);
            }
            if (!in_array($usage->status, [AlloUsageService::STATUS_PENDING, AlloUsageService::STATUS_ACCEPTED], true)) {
                return response()->json(['message' => 'Impossible d’attribuer cette demande.'], 422);
            }
            $this->assignUsage($usage, (int) $validated['handled_by_id']);
        }

        $usage->load(['allo', 'user', 'handledBy', 'doneBy']);

        return response()->json(['data' => $this->formatUsage($usage)]);
    }

    private function validateAllo(Request $request): array
    {
        $status = strtoupper((string) $request->input('status', ''));
        $requiresWindow = $status !== 'DRAFT';
        $normalizedTimeSlots = $this->normalizeTimeSlots($request->input('time_slots'));
        $payload = $request->all();
        $timeSlotDateRule = $requiresWindow
            ? ['required_with:time_slots', 'date_format:Y-m-d']
            : ['nullable', 'date_format:Y-m-d'];
        $timeSlotTimeRule = $requiresWindow
            ? ['required_with:time_slots', 'date_format:H:i']
            : ['nullable', 'date_format:H:i'];

        if ($normalizedTimeSlots !== null) {
            $payload['time_slots'] = $normalizedTimeSlots;
        }
        $validator = Validator::make($payload, [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status' => ['required', Rule::in(['DRAFT', 'OPEN', 'CLOSED', 'DISABLED'])],
            'window_start_at' => ['nullable', 'date'],
            'window_end_at' => ['nullable', 'date', 'after:window_start_at'],
            'slot_duration_minutes' => [Rule::requiredIf($requiresWindow), 'nullable', 'integer', 'min:1'],
            'security_margin_minutes' => ['nullable', 'integer', 'min:0'],
            'daily_booking_limit' => ['nullable', 'integer', 'min:1'],
            'slot_capacity' => ['nullable', 'integer', 'min:1'],
            'time_slots' => ['nullable', 'array'],
            'time_slots.*.start_date' => $timeSlotDateRule,
            'time_slots.*.end_date' => array_merge($timeSlotDateRule, ['after_or_equal:start_date']),
            'time_slots.*.start_time' => $timeSlotTimeRule,
            'time_slots.*.end_time' => $timeSlotTimeRule,
            'admin_ids' => ['nullable', 'array'],
            'admin_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $validator->after(function ($validator) use ($payload, $requiresWindow): void {
            $timeSlots = $payload['time_slots'] ?? [];
            $hasTimeSlots = is_array($timeSlots) && count($timeSlots) > 0;
            $hasWindow = !empty($payload['window_start_at']) && !empty($payload['window_end_at']);

            if ($requiresWindow && ! $hasWindow && is_array($timeSlots) && count($timeSlots) === 0 && array_key_exists('time_slots', $payload)) {
                $validator->errors()->add('time_slots', 'Au moins un créneau est requis.');
            }

            if ($requiresWindow && ! $hasTimeSlots && ! $hasWindow) {
                $validator->errors()->add('time_slots', 'Un créneau horaire ou une fenêtre globale est requis.');
            }

            if ($hasTimeSlots) {
                foreach ($timeSlots as $index => $slot) {
                    if (!is_array($slot)) {
                        continue;
                    }

                    $startTime = $slot['start_time'] ?? null;
                    $endTime = $slot['end_time'] ?? null;
                    $startDate = $slot['start_date'] ?? null;
                    $endDate = $slot['end_date'] ?? null;

                    if ($startDate !== null && $endDate !== null && $startDate > $endDate) {
                        $validator->errors()->add("time_slots.{$index}.end_date", 'La date de fin doit être après la date de début.');
                    }

                    if ($startTime !== null && $endTime !== null && $startTime >= $endTime) {
                        $validator->errors()->add("time_slots.{$index}.end_time", 'L’heure de fin doit être après l’heure de début.');
                    }
                }
            }
        });

        return $validator->validate();
    }

    /**
     * @return array<int, mixed>|null
     */
    private function normalizeTimeSlots(mixed $timeSlots): ?array
    {
        if ($timeSlots === null) {
            return null;
        }

        if (is_string($timeSlots)) {
            $decoded = json_decode($timeSlots, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $timeSlots = $decoded;
            }
        }

        if (!is_array($timeSlots)) {
            return null;
        }

        $hasSlotKeys = array_key_exists('start_date', $timeSlots)
            || array_key_exists('end_date', $timeSlots)
            || array_key_exists('start_time', $timeSlots)
            || array_key_exists('end_time', $timeSlots);

        if ($hasSlotKeys) {
            return [$timeSlots];
        }

        return $timeSlots;
    }

    private function resetUsageToPending(AlloUsage $usage): void
    {
        $usage->status = AlloUsageService::STATUS_PENDING;
        $usage->handled_by_id = null;
        $usage->accepted_at = null;
        $usage->done_by_id = null;
        $usage->done_at = null;
        $usage->cancelled_at = null;

        $usage->save();
    }

    private function assignUsage(AlloUsage $usage, int $handlerId): void
    {
        $wasPending = $usage->status === AlloUsageService::STATUS_PENDING;

        $usage->status = AlloUsageService::STATUS_ACCEPTED;
        $usage->handled_by_id = $handlerId;

        if ($wasPending || $usage->accepted_at === null) {
            $usage->accepted_at = Carbon::now();
        }

        $usage->save();
    }

    private function ensureHandlerIsAllowed(AlloUsage $usage, int $handlerId): void
    {
        $usage->loadMissing('allo.admins');
        $alloAdmins = $usage->allo?->admins;

        if ($alloAdmins !== null && $alloAdmins->isNotEmpty()) {
            if (!$alloAdmins->pluck('id')->contains($handlerId)) {
                abort(422, 'Cet admin ne fait pas partie des responsables de cet allo.');
            }

            return;
        }

        if (!$this->userHasAnyAdminRole($handlerId)) {
            abort(422, 'Cet admin ne fait pas partie des responsables de cet allo.');
        }
    }

    private function userHasAnyAdminRole(int $userId): bool
    {
        return DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('user_roles.user_id', $userId)
            ->whereIn('roles.name', self::ADMIN_ROLES)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatAllo(Allo $allo): array
    {
        $capacity = (int) $allo->slots()->sum('capacity');
        $bookedCount = (int) $allo->usages()
            ->whereIn('status', [
                AlloUsageService::STATUS_PENDING,
                AlloUsageService::STATUS_ACCEPTED,
                AlloUsageService::STATUS_DONE,
            ])
            ->count();

        return [
            'id' => $allo->id,
            'title' => $allo->title,
            'description' => $allo->description,
            'status' => $allo->status,
            'created_at' => $allo->created_at?->toIso8601String(),
            'capacity' => $capacity,
            'booked_count' => $bookedCount,
            'remaining' => max($capacity - $bookedCount, 0),
            'window_start_at' => $allo->window_start_at?->toIso8601String(),
            'window_end_at' => $allo->window_end_at?->toIso8601String(),
            'slot_duration_minutes' => $allo->slot_duration_minutes,
            'security_margin_minutes' => $allo->security_margin_minutes,
            'daily_booking_limit' => $allo->daily_booking_limit,
            'slot_capacity' => $allo->slot_capacity,
            'time_slots' => $allo->time_slots ?? [],
            'admins' => $allo->admins->map(fn ($admin): array => [
                'id' => $admin->id,
                'name' => $admin->display_name ?? $admin->university_email,
                'email' => $admin->university_email,
            ])->values(),
            'admin_ids' => $allo->admins->pluck('id')->values(),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function fetchVisitsCounts(Carbon $start, Carbon $end): array
    {
        return DB::table('allo_page_views')
            ->select([
                'allo_id',
                DB::raw('COUNT(DISTINCT user_id) as visitors'),
            ])
            ->whereBetween('viewed_at', [$start, $end])
            ->groupBy('allo_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->allo_id => (int) $row->visitors])
            ->all();
    }

    /**
     * @param  object  $row
     * @return array<string, mixed>
     */
    private function formatUsageRow(object $row): array
    {
        return [
            'id' => (int) $row->id,
            'allo_id' => (int) $row->allo_id,
            'allo_title' => $row->allo_title,
            'slot_start_at' => $this->formatDate($row->slot_start_at),
            'status' => $row->status,
            'created_at' => $this->formatDate($row->created_at),
            'accepted_at' => $this->formatDate($row->accepted_at),
            'done_at' => $this->formatDate($row->done_at),
            'cancelled_at' => $this->formatDate($row->cancelled_at),
            'user_note' => $row->user_note,
            'user_id' => (int) $row->user_id,
            'user_name' => $row->user_name,
            'user_email' => $row->user_email,
            'handled_by_id' => $row->handled_by_id ? (int) $row->handled_by_id : null,
            'handled_by_name' => $row->handled_by_name,
            'done_by_name' => $row->done_by_name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatUsage(AlloUsage $usage): array
    {
        return [
            'id' => $usage->id,
            'allo_id' => $usage->allo_id,
            'allo_title' => $usage->allo?->title,
            'slot_start_at' => $usage->slot_start_at?->toIso8601String(),
            'status' => $usage->status,
            'created_at' => $usage->created_at?->toIso8601String(),
            'accepted_at' => $usage->accepted_at?->toIso8601String(),
            'done_at' => $usage->done_at?->toIso8601String(),
            'cancelled_at' => $usage->cancelled_at?->toIso8601String(),
            'user_note' => $usage->user_note,
            'user_id' => $usage->user_id,
            'user_name' => $usage->user?->display_name ?? $usage->user?->university_email,
            'user_email' => $usage->user?->university_email,
            'handled_by_id' => $usage->handled_by_id,
            'handled_by_name' => $usage->handledBy?->display_name ?? $usage->handledBy?->university_email,
            'done_by_name' => $usage->doneBy?->display_name ?? $usage->doneBy?->university_email,
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->toIso8601String();
    }
}
