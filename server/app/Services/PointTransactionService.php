<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\User;

class PointTransactionService
{
    /**
     * Crée une transaction de points "manuelle" (vue Gamemaster).
     *
     * - amount > 0  => gain de points
     * - amount < 0  => retrait / dépense
     *
     * @param User $targetUser   Utilisateur qui gagne/perd des points.
     * @param User|null   $actor        Admin / gamemaster qui initie l'action (peut être null si système).
     * @param  int                     $amount       Nombre de points (+/-).
     * @param  string                  $reason       Libellé visible (ex: "Défi du jour", "Ambiance BDS", etc.).
     * @param  string|null             $contextType  Type de contexte (ex: "challenge", "allo", "manual").
     * @param  int|null                $contextId    Id de l'entité liée (challenge_id, allo_id, etc.).
     * @return PointTransaction
     */
    public function createManualTransaction(
        User $targetUser,
        ?User $actor,
        int $amount,
        string $reason,
        ?string $contextType = null,
        ?int $contextId = null,
    ): PointTransaction {
        return PointTransaction::query()->create([
            'user_id' => $targetUser->id,
            'amount' => $amount,
            'reason' => $reason,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'created_by_id' => $actor?->id,
        ]);
    }

    /**
     * Retourne le solde actuel de points pour un utilisateur.
     *
     * @param User $user
     * @return int
     */
    public function getUserBalance(User $user): int
    {
        /** @var int|float|string|null $sum */
        $sum = PointTransaction::query()
            ->where('user_id', $user->id)
            ->sum('amount');

        return (int) $sum;
    }
}
