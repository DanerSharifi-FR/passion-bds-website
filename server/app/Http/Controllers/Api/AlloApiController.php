<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Allo;
use App\Models\AlloSlot;
use App\Models\AlloUsage;
use App\Services\AlloUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlloApiController extends Controller
{
    /**
     * Liste des allos + slots disponibles pour le catalogue.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = now();

        $alloId = $request->query('allo_id');
        $slotsOnly = $request->boolean('slots_only');
        $bookingId = $request->query('booking_id');

        $editingBooking = null;
        if ($slotsOnly && $user !== null && $bookingId !== null && $bookingId !== '') {
            $editingBooking = AlloUsage::query()
                ->where('id', (int) $bookingId)
                ->where('user_id', $user->id)
                ->first();
        }

        $allos = Allo::query()
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->when($alloId, function ($query) use ($alloId): void {
                $query->where('id', (int) $alloId);
            })
            ->withCount('admins')
            ->with(['slots' => function ($query) use ($slotsOnly, $now): void {
                $query
                    ->where('slot_start_at', '>=', $now)
                    ->orderBy('slot_start_at')
                    ->withCount(['usages as bookings_count' => function ($usageQuery): void {
                        $usageQuery->whereIn('status', [
                            AlloUsageService::STATUS_PENDING,
                            AlloUsageService::STATUS_ACCEPTED,
                            AlloUsageService::STATUS_DONE,
                        ]);
                    }]);

                if ($slotsOnly) {
                    $query
                        ->where('slot_start_at', '>=', $now);
                }
            }])
            ->orderBy('window_start_at')
            ->get();

        $bookingsBySlotId = collect();
        $bookingCountsByAlloDate = collect();

        if ($user !== null) {
            $bookingsBySlotId = AlloUsage::query()
                ->where('user_id', $user->id)
                ->whereIn('allo_id', $allos->pluck('id'))
                ->get()
                ->keyBy('allo_slot_id');

            $bookingCountsByAlloDate = AlloUsage::query()
                ->selectRaw('allo_id, DATE(slot_start_at) as slot_date, COUNT(*) as bookings_count')
                ->where('user_id', $user->id)
                ->whereIn('allo_id', $allos->pluck('id'))
                ->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ])
                ->groupBy('allo_id', 'slot_date')
                ->get()
                ->groupBy('allo_id')
                ->map(function ($rows): array {
                    return $rows->mapWithKeys(function ($row): array {
                        return [$row->slot_date => (int) $row->bookings_count];
                    })->all();
                });
        }

        $payload = $allos->map(function (Allo $allo) use ($bookingsBySlotId, $bookingCountsByAlloDate, $editingBooking, $now, $slotsOnly, $user): array {
            $totalCapacity = (int) $allo->slots->sum('capacity');
            $bookedCount = (int) $allo->slots->sum(function (AlloSlot $slot): int {
                return (int) ($slot->bookings_count ?? 0);
            });
            $windowBounds = $this->resolveWindowBounds($allo);
            $windowStart = $windowBounds[0] ?? null;
            $windowEnd = $windowBounds[1] ?? null;
            $slotCapacityFallback = (int) $allo->admins_count;
            $securityMargin = max((int) ($allo->security_margin_minutes ?? 0), 0);
            $availabilityThreshold = $now->copy()->addMinutes($securityMargin);
            $editingSlotId = $slotsOnly && $editingBooking?->allo_id === $allo->id
                ? $editingBooking->allo_slot_id
                : null;

            $selectableSlots = $allo->slots->filter(function (AlloSlot $slot) use ($slotCapacityFallback, $availabilityThreshold, $editingSlotId, $bookingsBySlotId): bool {
                if ($editingSlotId !== null && $slot->id === $editingSlotId) {
                    return true;
                }

                if ($bookingsBySlotId->has($slot->id)) {
                    return true;
                }

                $capacity = (int) ($slot->capacity ?? $slotCapacityFallback);
                $bookingsCount = (int) ($slot->bookings_count ?? 0);
                $remaining = max($capacity - $bookingsCount, 0);

                if (! $slot->slot_start_at || $slot->slot_start_at->lessThan($availabilityThreshold)) {
                    return false;
                }

                return in_array($slot->status, ['available', 'partial'], true) && $remaining > 0;
            });

            $dailyLimit = $allo->daily_booking_limit;
            $canBookNew = true;

            if ($user !== null && $dailyLimit !== null && $dailyLimit > 0) {
                $bookingCountsByDate = $bookingCountsByAlloDate->get($allo->id, []);
                $canBookNew = $selectableSlots->contains(function (AlloSlot $slot) use ($bookingCountsByDate, $dailyLimit): bool {
                    $slotDate = $slot->slot_start_at?->format('Y-m-d');

                    if ($slotDate === null) {
                        return false;
                    }

                    $currentCount = (int) ($bookingCountsByDate[$slotDate] ?? 0);

                    return $currentCount < $dailyLimit;
                });
            }

            $disabledDates = [];

            if ($slotsOnly) {
                $selectableDates = $selectableSlots
                    ->map(fn (AlloSlot $slot): ?string => $slot->slot_start_at?->format('Y-m-d'))
                    ->filter()
                    ->unique();

                $disabledDates = $allo->slots
                    ->map(fn (AlloSlot $slot): ?string => $slot->slot_start_at?->format('Y-m-d'))
                    ->filter()
                    ->unique()
                    ->diff($selectableDates)
                    ->values()
                    ->all();
            }

            $userSlotIds = $allo->slots
                ->pluck('id')
                ->filter(fn ($slotId) => $bookingsBySlotId->has($slotId))
                ->values()
                ->all();
            $hasAvailableSlots = $this->hasAvailableSlots($allo, $now);
            $hasAlternativeSlots = $userSlotIds !== []
                ? $this->hasAvailableSlots($allo, $now, $userSlotIds)
                : $hasAvailableSlots;

            $slots = $allo->slots->map(function (AlloSlot $slot) use ($bookingsBySlotId, $allo): array {
                /** @var AlloUsage|null $booking */
                $booking = $bookingsBySlotId->get($slot->id);
                $capacity = (int) ($slot->capacity ?? $allo->admins_count);
                $bookingsCount = (int) ($slot->bookings_count ?? 0);
                $remaining = max($capacity - $bookingsCount, 0);

                return [
                    'id' => $slot->id,
                    'slot_start_at' => $slot->slot_start_at?->toIso8601String(),
                    'slot_end_at' => $slot->slot_end_at?->toIso8601String(),
                    'status' => $slot->status,
                    'capacity' => $capacity,
                    'booked_count' => $bookingsCount,
                    'remaining' => $remaining,
                    'bookings_count' => $bookingsCount,
                    'remaining_capacity' => $remaining,
                    'user_booking' => $booking ? [
                        'id' => $booking->id,
                        'status' => $booking->status,
                        'user_note' => $booking->user_note,
                        'slot_start_at' => $booking->slot_start_at?->toIso8601String(),
                    ] : null,
                ];
            });

            $slots = $slots
                ->groupBy(function (array $slot): ?string {
                    if (! $slot['slot_start_at']) {
                        return null;
                    }

                    return Carbon::parse($slot['slot_start_at'])->toDateString();
                })
                ->filter(function ($daySlots, $dateKey) use ($availabilityThreshold): bool {
                    if ($dateKey === null) {
                        return false;
                    }

                    return $daySlots->contains(function (array $slot) use ($availabilityThreshold): bool {
                        if (! $slot['slot_start_at']) {
                            return false;
                        }

                        $slotStart = Carbon::parse($slot['slot_start_at']);
                        $remaining = (int) ($slot['remaining'] ?? 0);

                        return $slotStart->greaterThanOrEqualTo($availabilityThreshold)
                            && in_array($slot['status'], ['available', 'partial'], true)
                            && $remaining > 0;
                    });
                })
                ->flatten(1)
                ->values();

            return [
                'id' => $allo->id,
                'title' => $allo->title,
                'description' => $allo->description,
                'status' => $allo->status,
                'capacity' => $totalCapacity,
                'booked_count' => $bookedCount,
                'remaining' => max($totalCapacity - $bookedCount, 0),
                'window_start_at' => $windowStart?->toIso8601String(),
                'window_end_at' => $windowEnd?->toIso8601String(),
                'slot_duration_minutes' => $allo->slot_duration_minutes,
                'security_margin_minutes' => $securityMargin,
                'daily_booking_limit' => $dailyLimit,
                'slot_capacity' => (int) $allo->admins_count,
                'time_slots' => $allo->time_slots ?? [],
                'is_window_open' => $windowEnd !== null
                    && $now->lessThanOrEqualTo($windowEnd),
                'is_window_ended' => $windowEnd !== null && $now->greaterThan($windowEnd),
                'can_book_new' => $canBookNew,
                'has_available_slots' => $hasAvailableSlots,
                'has_alternative_slots' => $hasAlternativeSlots,
                'disabled_dates' => $slotsOnly ? $disabledDates : [],
                'slots' => ($slotsOnly ? $selectableSlots : $allo->slots)->map(function (AlloSlot $slot) use ($bookingsBySlotId, $allo, $slotCapacityFallback): array {
                    /** @var AlloUsage|null $booking */
                    $booking = $bookingsBySlotId->get($slot->id);
                    $capacity = (int) ($slot->capacity ?? $slotCapacityFallback);
                    $bookingsCount = (int) ($slot->bookings_count ?? 0);
                    $remaining = max($capacity - $bookingsCount, 0);

                    return [
                        'id' => $slot->id,
                        'slot_start_at' => $slot->slot_start_at?->toIso8601String(),
                        'slot_end_at' => $slot->slot_end_at?->toIso8601String(),
                        'status' => $slot->status,
                        'capacity' => $capacity,
                        'booked_count' => $bookingsCount,
                        'remaining' => $remaining,
                        'bookings_count' => $bookingsCount,
                        'remaining_capacity' => $remaining,
                        'user_booking' => $booking ? [
                            'id' => $booking->id,
                            'status' => $booking->status,
                            'user_note' => $booking->user_note,
                            'slot_start_at' => $booking->slot_start_at?->toIso8601String(),
                        ] : null,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'allos' => $payload,
        ]);
    }

    /**
     * Réserve un créneau d'allo pour un étudiant.
     */
    public function storeBooking(
        Request $request,
    ): JsonResponse {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $validated = $request->validate([
            'allo_id' => ['required', 'integer', Rule::exists('allos', 'id')],
            'allo_slot_id' => ['required', 'integer', Rule::exists('allo_slots', 'id')],
            'user_note' => ['nullable', 'string', 'max:500'],
        ]);

        $now = now();

        /** @var Allo $allo */
        $allo = Allo::query()
            ->where('status', 'OPEN')
            ->findOrFail((int) $validated['allo_id']);

        $windowBounds = $this->resolveWindowBounds($allo);
        $windowStart = $windowBounds[0] ?? null;
        $windowEnd = $windowBounds[1] ?? null;

        if ($windowStart === null || $windowEnd === null) {
            return response()->json(['message' => 'Cet allo est indisponible.'], 422);
        }

        if ($now->greaterThan($windowEnd)) {
            return response()->json(['message' => 'Les réservations pour cet allo sont fermées.'], 422);
        }

        /** @var AlloSlot $slot */
        $slot = AlloSlot::query()
            ->where('id', (int) $validated['allo_slot_id'])
            ->where('allo_id', $allo->id)
            ->firstOrFail();

        $availabilityThreshold = $now->copy()->addMinutes(max((int) ($allo->security_margin_minutes ?? 0), 0));

        if ($slot->slot_start_at !== null) {
            if ($slot->slot_start_at->lessThan($now)) {
                return response()->json(['message' => 'Ce créneau est déjà passé.'], 422);
            }

            if ($slot->slot_start_at->lessThan($availabilityThreshold)) {
                return response()->json(['message' => "Ce créneau n'est pas encore disponible."], 422);
            }
        }

        if ($slot->status === 'blocked') {
            return response()->json(['message' => 'Ce créneau est bloqué.'], 422);
        }

        $dailyLimit = $allo->daily_booking_limit;
        $slotDate = $slot->slot_start_at?->toDateString();
        $dailyLimitReached = $slotDate !== null
            && $dailyLimit !== null
            && $dailyLimit > 0
            && AlloUsage::query()
                ->where('user_id', $user->id)
                ->where('allo_id', $allo->id)
                ->whereDate('slot_start_at', $slotDate)
                ->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ])
                ->count() >= $dailyLimit;

        if ($dailyLimitReached) {
            return response()->json(['message' => 'Vous avez atteint la limite de réservations pour cet allo ce jour-là.'], 422);
        }

        $booking = DB::transaction(function () use ($user, $allo, $slot, $validated): AlloUsage {
            $slot = AlloSlot::query()
                ->where('id', $slot->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->status === 'blocked') {
                abort(422, 'Ce créneau est bloqué.');
            }

            $slotCapacity = $allo->admins()->count();

            if ($slotCapacity <= 0) {
                abort(422, 'Ce créneau est indisponible.');
            }

            $currentBookings = AlloUsage::query()
                ->where('allo_slot_id', $slot->id)
                ->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ])
                ->count();

            if ($currentBookings >= $slotCapacity) {
                $slot->status = 'booked';
                $slot->save();

                abort(422, 'Ce créneau est déjà réservé.');
            }

            $usage = AlloUsage::query()->create([
                'allo_id' => $allo->id,
                'allo_slot_id' => $slot->id,
                'slot_start_at' => $slot->slot_start_at,
                'user_id' => $user->id,
                'user_note' => $validated['user_note'] ?? null,
                'status' => AlloUsageService::STATUS_PENDING,
            ]);

            $newCount = $currentBookings + 1;
            $slot->status = $newCount >= $slotCapacity ? 'booked' : 'available';
            $slot->save();

            return $usage;
        });

        return response()->json([
            'message' => 'Réservation enregistrée.',
            'booking' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'user_note' => $booking->user_note,
            ],
        ], 201);
    }

    public function bookings(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $now = now();

        $bookings = AlloUsage::query()
            ->where('user_id', $user->id)
            ->with([
                'slot',
                'allo' => function ($query): void {
                    $query
                        ->withCount('admins')
                        ->with(['slots' => function ($slotQuery): void {
                            $slotQuery->withCount(['usages as bookings_count' => function ($usageQuery): void {
                                $usageQuery->whereIn('status', [
                                    AlloUsageService::STATUS_PENDING,
                                    AlloUsageService::STATUS_ACCEPTED,
                                    AlloUsageService::STATUS_DONE,
                                ]);
                            }]);
                        }]);
                },
            ])
            ->orderBy('slot_start_at')
            ->get()
            ->map(function (AlloUsage $usage) use ($now): array {
                return [
                    'id' => $usage->id,
                    'status' => $usage->status,
                    'user_note' => $usage->user_note,
                    'slot_start_at' => $usage->slot_start_at?->toIso8601String(),
                    'slot_end_at' => $usage->slot?->slot_end_at?->toIso8601String(),
                    'allo_id' => $usage->allo_id,
                    'allo_title' => $usage->allo?->title,
                    'allo_description' => $usage->allo?->description,
                    'slot_id' => $usage->allo_slot_id,
                    'can_edit' => $this->canEditBooking($usage, $now),
                    'has_available_slots' => $usage->allo
                        ? $this->hasAvailableSlots($usage->allo, $now, [$usage->allo_slot_id])
                        : false,
                ];
            })
            ->values();

        return response()->json([
            'bookings' => $bookings,
        ]);
    }

    private function canEditBooking(AlloUsage $usage, Carbon $now): bool
    {
        if ($usage->status !== AlloUsageService::STATUS_PENDING) {
            return false;
        }

        $allo = $usage->allo;

        if ($allo === null || $allo->status !== 'OPEN') {
            return false;
        }

        $windowBounds = $this->resolveWindowBounds($allo);
        $windowEnd = $windowBounds[1] ?? null;

        if ($windowEnd === null || $now->greaterThan($windowEnd)) {
            return false;
        }

        return true;
    }

    public function updateBooking(Request $request, AlloUsage $booking): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($booking->status !== AlloUsageService::STATUS_PENDING) {
            return response()->json(['message' => "Cette réservation n'est pas modifiable."], 422);
        }

        $validated = $request->validate([
            'allo_slot_id' => ['required', 'integer', Rule::exists('allo_slots', 'id')],
            'user_note' => ['nullable', 'string', 'max:500'],
        ]);

        $now = now();
        /** @var Allo $allo */
        $allo = Allo::query()
            ->where('status', 'OPEN')
            ->findOrFail((int) $booking->allo_id);

        $windowBounds = $this->resolveWindowBounds($allo);
        $windowEnd = $windowBounds[1] ?? null;

        if ($windowEnd !== null && $now->greaterThan($windowEnd)) {
            return response()->json(['message' => 'Les modifications pour cet allo sont fermées.'], 422);
        }

        /** @var AlloSlot $slot */
        $slot = AlloSlot::query()
            ->where('id', (int) $validated['allo_slot_id'])
            ->where('allo_id', $allo->id)
            ->firstOrFail();

        $availabilityThreshold = $now->copy()->addMinutes(max((int) ($allo->security_margin_minutes ?? 0), 0));
        $isSameSlot = $slot->id === $booking->allo_slot_id;

        if (! $isSameSlot && $slot->slot_start_at !== null) {
            if ($slot->slot_start_at->lessThan($now)) {
                return response()->json(['message' => 'Ce créneau est déjà passé.'], 422);
            }

            if ($slot->slot_start_at->lessThan($availabilityThreshold)) {
                return response()->json(['message' => "Ce créneau n'est pas encore disponible."], 422);
            }
        }

        if ($slot->status === 'blocked') {
            return response()->json(['message' => 'Ce créneau est bloqué.'], 422);
        }

        $dailyLimit = $allo->daily_booking_limit;
        $slotDate = $slot->slot_start_at?->toDateString();
        $dailyLimitReached = $slotDate !== null
            && $dailyLimit !== null
            && $dailyLimit > 0
            && AlloUsage::query()
                ->where('user_id', $user->id)
                ->where('allo_id', $allo->id)
                ->whereDate('slot_start_at', $slotDate)
                ->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ])
                ->where('id', '!=', $booking->id)
                ->count() >= $dailyLimit;

        if ($dailyLimitReached) {
            return response()->json(['message' => 'Vous avez atteint la limite de réservations pour cet allo ce jour-là.'], 422);
        }

        $updatedBooking = DB::transaction(function () use ($allo, $booking, $slot, $validated): AlloUsage {
            $originalSlotId = $booking->allo_slot_id;

            $lockedSlots = AlloSlot::query()
                ->whereIn('id', array_unique([$originalSlotId, $slot->id]))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $lockedSlot = $lockedSlots->get($slot->id);

            if ($lockedSlot === null) {
                abort(422, 'Ce créneau est indisponible.');
            }

            if ($lockedSlot->status === 'blocked') {
                abort(422, 'Ce créneau est bloqué.');
            }

            $slotCapacity = $allo->admins()->count();

            if ($slotCapacity <= 0) {
                abort(422, 'Ce créneau est indisponible.');
            }

            $currentBookings = AlloUsage::query()
                ->where('allo_slot_id', $lockedSlot->id)
                ->whereIn('status', [
                    AlloUsageService::STATUS_PENDING,
                    AlloUsageService::STATUS_ACCEPTED,
                    AlloUsageService::STATUS_DONE,
                ])
                ->where('id', '!=', $booking->id)
                ->count();

            if ($currentBookings >= $slotCapacity) {
                $lockedSlot->status = 'booked';
                $lockedSlot->save();

                abort(422, 'Ce créneau est déjà réservé.');
            }

            $booking->allo_slot_id = $lockedSlot->id;
            $booking->slot_start_at = $lockedSlot->slot_start_at;
            $booking->user_note = $validated['user_note'] ?? null;
            $booking->save();

            $newCount = $currentBookings + 1;
            $lockedSlot->status = $newCount >= $slotCapacity ? 'booked' : 'available';
            $lockedSlot->save();

            if ($originalSlotId !== $lockedSlot->id) {
                $this->updateSlotStatus($originalSlotId, $slotCapacity);
            }

            return $booking;
        });

        return response()->json([
            'message' => 'Réservation mise à jour.',
            'booking' => [
                'id' => $updatedBooking->id,
                'status' => $updatedBooking->status,
                'user_note' => $updatedBooking->user_note,
            ],
        ]);
    }

    public function cancelBooking(Request $request, AlloUsage $booking, AlloUsageService $usageService): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        if ($booking->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($booking->status !== AlloUsageService::STATUS_PENDING) {
            return response()->json(['message' => "Cette réservation ne peut pas être annulée."], 422);
        }

        $updatedBooking = $usageService->cancel($booking, $user);

        return response()->json([
            'booking' => [
                'id' => $updatedBooking->id,
                'status' => $updatedBooking->status,
                'cancelled_at' => $updatedBooking->cancelled_at?->toIso8601String(),
            ],
        ]);
    }

    private function hasAvailableSlots(Allo $allo, Carbon $now, array $excludeSlotIds = []): bool
    {
        if (! $allo->relationLoaded('slots')) {
            return false;
        }

        $securityMargin = max((int) ($allo->security_margin_minutes ?? 0), 0);
        $availabilityThreshold = $now->copy()->addMinutes($securityMargin);
        $slotCapacityFallback = (int) ($allo->admins_count ?? 0);

        return $allo->slots->contains(function (AlloSlot $slot) use ($availabilityThreshold, $slotCapacityFallback, $excludeSlotIds): bool {
            if (in_array($slot->id, $excludeSlotIds, true)) {
                return false;
            }

            if (! $slot->slot_start_at || $slot->slot_start_at->lessThan($availabilityThreshold)) {
                return false;
            }

            if (! in_array($slot->status, ['available', 'partial'], true)) {
                return false;
            }

            $capacity = (int) ($slot->capacity ?? $slotCapacityFallback);
            $bookingsCount = (int) ($slot->bookings_count ?? 0);
            $remaining = max($capacity - $bookingsCount, 0);

            return $remaining > 0;
        });
    }

    /**
     * @return array{0: Carbon, 1: Carbon}|null
     */
    private function resolveWindowBounds(Allo $allo): ?array
    {
        if ($allo->window_start_at !== null && $allo->window_end_at !== null) {
            return [$allo->window_start_at, $allo->window_end_at];
        }

        $timeSlots = $allo->time_slots;

        if (!is_array($timeSlots) || count($timeSlots) === 0) {
            return null;
        }

        $minStart = null;
        $maxEnd = null;

        foreach ($timeSlots as $slot) {
            if (!is_array($slot)) {
                continue;
            }

            $startDate = $slot['start_date'] ?? null;
            $endDate = $slot['end_date'] ?? null;
            $startTime = $slot['start_time'] ?? null;
            $endTime = $slot['end_time'] ?? null;

            if (!is_string($startDate) || !is_string($endDate) || !is_string($startTime) || !is_string($endTime)) {
                continue;
            }

            $start = Carbon::createFromFormat('Y-m-d H:i', "{$startDate} {$startTime}");
            $end = Carbon::createFromFormat('Y-m-d H:i', "{$endDate} {$endTime}");

            if (! $start instanceof Carbon || ! $end instanceof Carbon) {
                continue;
            }

            $minStart = $minStart === null || $start->lessThan($minStart) ? $start : $minStart;
            $maxEnd = $maxEnd === null || $end->greaterThan($maxEnd) ? $end : $maxEnd;
        }

        if ($minStart === null || $maxEnd === null) {
            return null;
        }

        return [$minStart, $maxEnd];
    }

    private function updateSlotStatus(int $slotId, int $slotCapacity): void
    {
        $slot = AlloSlot::query()->find($slotId);

        if ($slot === null) {
            return;
        }

        if ($slot->status === 'blocked') {
            return;
        }

        $currentBookings = AlloUsage::query()
            ->where('allo_slot_id', $slot->id)
            ->whereIn('status', [
                AlloUsageService::STATUS_PENDING,
                AlloUsageService::STATUS_ACCEPTED,
                AlloUsageService::STATUS_DONE,
            ])
            ->count();

        $slot->status = $currentBookings >= $slotCapacity ? 'booked' : 'available';
        $slot->save();
    }
}
