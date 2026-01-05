<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Allo;
use App\Models\AlloSlot;
use App\Models\AlloUsage;
use App\Services\AlloUsageService;
use App\Services\PointTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $allos = Allo::query()
            ->whereIn('status', ['OPEN', 'CLOSED'])
            ->withCount('admins')
            ->with(['slots' => function ($query): void {
                $query
                    ->orderBy('slot_start_at')
                    ->withCount(['usages as bookings_count' => function ($usageQuery): void {
                        $usageQuery->whereIn('status', [
                            AlloUsageService::STATUS_PENDING,
                            AlloUsageService::STATUS_ACCEPTED,
                            AlloUsageService::STATUS_DONE,
                        ]);
                    }]);
            }])
            ->orderBy('window_start_at')
            ->get();

        $bookingsBySlotId = collect();

        if ($user !== null) {
            $bookingsBySlotId = AlloUsage::query()
                ->where('user_id', $user->id)
                ->whereIn('allo_id', $allos->pluck('id'))
                ->get()
                ->keyBy('allo_slot_id');
        }

        $payload = $allos->map(function (Allo $allo) use ($bookingsBySlotId, $now): array {
            $totalCapacity = (int) $allo->slots->sum('capacity');
            $bookedCount = (int) $allo->slots->sum(function (AlloSlot $slot): int {
                return (int) ($slot->bookings_count ?? 0);
            });

            return [
                'id' => $allo->id,
                'title' => $allo->title,
                'description' => $allo->description,
                'points_cost' => $allo->points_cost,
                'status' => $allo->status,
                'capacity' => $totalCapacity,
                'booked_count' => $bookedCount,
                'remaining' => max($totalCapacity - $bookedCount, 0),
                'window_start_at' => optional($allo->window_start_at)->toIso8601String(),
                'window_end_at' => optional($allo->window_end_at)->toIso8601String(),
                'slot_duration_minutes' => $allo->slot_duration_minutes,
                'slot_capacity' => (int) $allo->admins_count,
                'is_window_open' => $allo->window_start_at !== null
                    && $allo->window_end_at !== null
                    && $now->between($allo->window_start_at, $allo->window_end_at),
                'is_window_ended' => $allo->window_end_at !== null && $now->greaterThan($allo->window_end_at),
                'slots' => $allo->slots->map(function (AlloSlot $slot) use ($bookingsBySlotId, $allo): array {
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
        PointTransactionService $pointTransactionService,
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

        if ($allo->window_start_at === null || $allo->window_end_at === null) {
            return response()->json(['message' => 'Cet allo est indisponible.'], 422);
        }

        if (! $now->between($allo->window_start_at, $allo->window_end_at)) {
            return response()->json(['message' => 'Les réservations pour cet allo sont fermées.'], 422);
        }

        /** @var AlloSlot $slot */
        $slot = AlloSlot::query()
            ->where('id', (int) $validated['allo_slot_id'])
            ->where('allo_id', $allo->id)
            ->firstOrFail();

        if ($slot->slot_start_at !== null && $slot->slot_start_at->lessThan($now)) {
            return response()->json(['message' => 'Ce créneau est déjà passé.'], 422);
        }

        if ($slot->status === 'blocked') {
            return response()->json(['message' => 'Ce créneau est bloqué.'], 422);
        }

        $balance = $pointTransactionService->getUserBalance($user);

        if ($balance < $allo->points_cost) {
            return response()->json(['message' => 'Points insuffisants pour réserver cet allo.'], 422);
        }

        $existingBooking = AlloUsage::query()
            ->where('user_id', $user->id)
            ->where('slot_start_at', $slot->slot_start_at)
            ->exists();

        if ($existingBooking) {
            return response()->json(['message' => 'Vous avez déjà réservé ce créneau.'], 422);
        }

        $booking = DB::transaction(function () use ($user, $allo, $slot, $validated, $pointTransactionService): AlloUsage {
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
                'points_spent' => $allo->points_cost,
                'user_note' => $validated['user_note'] ?? null,
                'status' => AlloUsageService::STATUS_PENDING,
            ]);

            $newCount = $currentBookings + 1;
            $slot->status = $newCount >= $slotCapacity ? 'booked' : 'available';
            $slot->save();

            $pointTransactionService->createManualTransaction(
                targetUser: $user,
                actor: null,
                amount: -$allo->points_cost,
                reason: sprintf('Réservation allo : %s', $allo->title),
                contextType: 'allo',
                contextId: $allo->id,
            );

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
}
