<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AlloUsage;
use App\Models\User;
use Illuminate\Support\Carbon;

class AlloUsageService
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_DONE      = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Accepte une demande d'allo.
     *
     * - Passe le statut à "accepted" si la demande est actuellement "pending".
     * - Renseigne handled_by_id avec l’admin qui prend en charge.
     * - Renseigne accepted_at avec l’horodatage courant.
     *
     * @param AlloUsage $usage
     * @param User|null  $actor
     * @return AlloUsage
     */
    public function accept(AlloUsage $usage, ?User $actor = null): AlloUsage
    {
        if ($usage->status !== self::STATUS_PENDING) {
            // Transition non autorisée : on ne touche pas.
            return $usage;
        }

        $usage->status = self::STATUS_ACCEPTED;
        $usage->handled_by_id = $actor?->id;
        $usage->accepted_at = Carbon::now();

        $usage->save();

        return $usage;
    }

    /**
     * Marque une demande comme réalisée ("done").
     *
     * - Passe le statut à "done" si la demande est "accepted".
     * - Renseigne done_by_id avec l’admin qui valide.
     * - Renseigne done_at avec l’horodatage courant.
     *
     * @param AlloUsage $usage
     * @param User|null  $actor
     * @return AlloUsage
     */
    public function markDone(AlloUsage $usage, ?User $actor = null): AlloUsage
    {
        if ($usage->status !== self::STATUS_ACCEPTED) {
            // On ne peut marquer comme done que ce qui a été accepté.
            return $usage;
        }

        $usage->status = self::STATUS_DONE;
        $usage->done_by_id = $actor?->id;
        $usage->done_at = Carbon::now();

        $usage->save();

        return $usage;
    }

    /**
     * Annule une demande d'allo.
     *
     * - Passe le statut à "cancelled" si la demande n’est pas déjà "done" ou "cancelled".
     * - Renseigne cancelled_at avec l’horodatage courant.
     * - Si aucun handled_by_id n’est défini, on met l’admin qui annule.
     *
     * @param AlloUsage $usage
     * @param  User|null  $actor
     * @return AlloUsage
     */
    public function cancel(AlloUsage $usage, ?User $actor = null): AlloUsage
    {
        if ($usage->status === self::STATUS_DONE || $usage->status === self::STATUS_CANCELLED) {
            // On ne touche pas à une demande déjà terminée ou annulée.
            return $usage;
        }

        $usage->status = self::STATUS_CANCELLED;

        if ($usage->handled_by_id === null && $actor !== null) {
            $usage->handled_by_id = $actor->id;
        }

        $usage->cancelled_at = Carbon::now();

        $usage->save();

        return $usage;
    }
}
