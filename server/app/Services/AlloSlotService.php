<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Allo;
use App\Models\AlloSlot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AlloSlotService
{
    /**
     * Génère des slots pour un allo à partir de sa fenêtre et de la durée.
     *
     * Règles :
     * - Utilise window_start_at, window_end_at et slot_duration_minutes de l’allo.
     * - Ne crée pas de doublon : si un slot existe déjà pour un start_at donné, on le saute.
     * - Crée les slots avec le statut "available" par défaut.
     *
     * @param  \App\Models\Allo  $allo
     * @return int  Nombre de slots créés.
     */
    public function generateSlotsForAllo(Allo $allo): int
    {
        if ($allo->window_start_at === null || $allo->window_end_at === null) {
            return 0;
        }

        if ($allo->slot_duration_minutes <= 0) {
            return 0;
        }

        /** @var Carbon $windowStart */
        $windowStart = Carbon::parse($allo->window_start_at);
        /** @var Carbon $windowEnd */
        $windowEnd = Carbon::parse($allo->window_end_at);

        if ($windowStart->greaterThanOrEqualTo($windowEnd)) {
            return 0;
        }

        // On récupère les start_at existants pour cet allo afin d’éviter les doublons.
        /** @var Collection<int, string> $existingStartTimes */
        $existingStartTimes = AlloSlot::query()
            ->where('allo_id', $allo->id)
            ->pluck('slot_start_at')
            ->map(static fn ($value): string => Carbon::parse($value)->toDateTimeString());

        /** @var array<int, string> $existingStartTimesArray */
        $existingStartTimesArray = $existingStartTimes->all();

        $createdCount = 0;

        /** @var Carbon $currentStart */
        $currentStart = $windowStart->copy();

        while ($currentStart->lessThan($windowEnd)) {
            /** @var Carbon $currentEnd */
            $currentEnd = $currentStart->copy()->addMinutes($allo->slot_duration_minutes);

            if ($currentEnd->greaterThan($windowEnd)) {
                // On s’arrête si le dernier slot dépasserait la fenêtre
                break;
            }

            $startString = $currentStart->toDateTimeString();

            if (in_array($startString, $existingStartTimesArray, true)) {
                // Slot déjà existant : on avance simplement.
                $currentStart = $currentEnd;

                continue;
            }

            AlloSlot::query()->create([
                'allo_id' => $allo->id,
                'slot_start_at' => $currentStart,
                'slot_end_at' => $currentEnd,
                'status' => 'available',
            ]);

            $createdCount++;
            $currentStart = $currentEnd;
        }

        return $createdCount;
    }
}
