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
use Illuminate\Validation\Rule;

class AlloApiController extends Controller
{
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
            ->whereIn('r.name', ['ROLE_SUPER_ADMIN', 'ROLE_GAMEMASTER'])
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
                'au.points_spent',
                'au.status',
                'au.created_at',
                'au.accepted_at',
                'au.done_at',
                'au.cancelled_at',
                'au.user_note',
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

    public function updateUsage(Request $request, AlloUsage $usage, AlloUsageService $usageService)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                AlloUsageService::STATUS_PENDING,
                AlloUsageService::STATUS_ACCEPTED,
                AlloUsageService::STATUS_DONE,
                AlloUsageService::STATUS_CANCELLED,
            ])],
        ]);

        if (!array_key_exists('status', $validated)) {
            return response()->json(['message' => 'Aucune modification demandée.'], 422);
        }

        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $status = $validated['status'];

            if ($status === AlloUsageService::STATUS_PENDING) {
                $this->resetUsageToPending($usage);
            } elseif ($status === AlloUsageService::STATUS_ACCEPTED) {
                $usageService->accept($usage, $request->user());
            } elseif ($status === AlloUsageService::STATUS_DONE) {
                $usageService->markDone($usage, $request->user());
            } elseif ($status === AlloUsageService::STATUS_CANCELLED) {
                $usageService->cancel($usage, $request->user());
            }
        }

        $usage->load(['allo', 'user', 'handledBy', 'doneBy']);

        return response()->json(['data' => $this->formatUsage($usage)]);
    }

    private function validateAllo(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'points_cost' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['DRAFT', 'OPEN', 'CLOSED', 'DISABLED'])],
            'window_start_at' => ['required', 'date'],
            'window_end_at' => ['required', 'date', 'after:window_start_at'],
            'slot_duration_minutes' => ['required', 'integer', 'min:1'],
            'admin_ids' => ['nullable', 'array'],
            'admin_ids.*' => ['integer', 'exists:users,id'],
        ]);
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

    /**
     * @return array<string, mixed>
     */
    private function formatAllo(Allo $allo): array
    {
        return [
            'id' => $allo->id,
            'title' => $allo->title,
            'description' => $allo->description,
            'points_cost' => $allo->points_cost,
            'status' => $allo->status,
            'window_start_at' => $allo->window_start_at?->toIso8601String(),
            'window_end_at' => $allo->window_end_at?->toIso8601String(),
            'slot_duration_minutes' => $allo->slot_duration_minutes,
            'admins' => $allo->admins->map(fn ($admin): array => [
                'id' => $admin->id,
                'name' => $admin->display_name ?? $admin->university_email,
                'email' => $admin->university_email,
            ])->values(),
            'admin_ids' => $allo->admins->pluck('id')->values(),
        ];
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
            'points_spent' => (int) $row->points_spent,
            'status' => $row->status,
            'created_at' => $this->formatDate($row->created_at),
            'accepted_at' => $this->formatDate($row->accepted_at),
            'done_at' => $this->formatDate($row->done_at),
            'cancelled_at' => $this->formatDate($row->cancelled_at),
            'user_note' => $row->user_note,
            'user_id' => (int) $row->user_id,
            'user_name' => $row->user_name,
            'user_email' => $row->user_email,
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
            'points_spent' => $usage->points_spent,
            'status' => $usage->status,
            'created_at' => $usage->created_at?->toIso8601String(),
            'accepted_at' => $usage->accepted_at?->toIso8601String(),
            'done_at' => $usage->done_at?->toIso8601String(),
            'cancelled_at' => $usage->cancelled_at?->toIso8601String(),
            'user_note' => $usage->user_note,
            'user_id' => $usage->user_id,
            'user_name' => $usage->user?->display_name ?? $usage->user?->university_email,
            'user_email' => $usage->user?->university_email,
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
