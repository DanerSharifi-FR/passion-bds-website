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
        if ($allo->slot_duration_minutes <= 0) {
            return 0;
        }

        $capacity = $allo->admins()->count();

        // On récupère les start_at existants pour cet allo afin d’éviter les doublons.
        /** @var Collection<int, string> $existingStartTimes */
        $existingStartTimes = AlloSlot::query()
            ->where('allo_id', $allo->id)
            ->pluck('slot_start_at')
            ->map(static fn ($value): string => Carbon::parse($value)->toDateTimeString());

        /** @var array<int, string> $existingStartTimesArray */
        $existingStartTimesArray = $existingStartTimes->all();

        AlloSlot::query()
            ->where('allo_id', $allo->id)
            ->update(['capacity' => $capacity]);

        $createdCount = 0;

        foreach ($this->resolveSlotWindows($allo) as [$windowStart, $windowEnd]) {
            if ($windowStart->greaterThanOrEqualTo($windowEnd)) {
                continue;
            }

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
                    'capacity' => $capacity,
                ]);

                $createdCount++;
                $currentStart = $currentEnd;
            }
        }

        return $createdCount;
    }

    /**
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    private function resolveSlotWindows(Allo $allo): array
    {
        $timeSlots = $allo->time_slots;

        if (is_array($timeSlots) && count($timeSlots) > 0) {
            $windows = [];

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

                $currentDate = Carbon::createFromFormat('Y-m-d', $startDate);
                $endDateCarbon = Carbon::createFromFormat('Y-m-d', $endDate);

                if (! $currentDate instanceof Carbon || ! $endDateCarbon instanceof Carbon) {
                    continue;
                }

                $currentDate = $currentDate->startOfDay();
                $endDateCarbon = $endDateCarbon->startOfDay();

                while ($currentDate->lessThanOrEqualTo($endDateCarbon)) {
                    $windowStart = $currentDate->copy()->setTimeFromTimeString($startTime);
                    $windowEnd = $currentDate->copy()->setTimeFromTimeString($endTime);

                    $windows[] = [$windowStart, $windowEnd];
                    $currentDate->addDay();
                }
            }

            return $windows;
        }

        if ($allo->window_start_at === null || $allo->window_end_at === null) {
            return [];
        }

        /** @var Carbon $windowStart */
        $windowStart = Carbon::parse($allo->window_start_at);
        /** @var Carbon $windowEnd */
        $windowEnd = Carbon::parse($allo->window_end_at);

        return [[$windowStart, $windowEnd]];
    }
}
