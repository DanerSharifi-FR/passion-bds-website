<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BetBetStatus;
use App\Enums\WalletTransactionType;
use App\Models\BetMatch;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminBetMatchService
{
    /**
     * Supprime un match et rembourse les mises actives.
     *
     * @param \App\Models\User $admin
     * @param int $matchId
     * @return int Nombre d'utilisateurs remboursés
     *
     * @throws ValidationException
     */
    public function deleteMatchAndRefund(\App\Models\User $admin, int $matchId): int
    {
        return DB::transaction(function () use ($admin, $matchId): int {
            $match = BetMatch::query()
                ->whereKey($matchId)
                ->lockForUpdate()
                ->firstOrFail();

            $settledCount = DB::table('bet_bets')
                ->where('match_id', $match->id)
                ->where('status', BetBetStatus::SETTLED->value)
                ->count();

            if ($settledCount > 0) {
                throw ValidationException::withMessages([
                    'match' => 'Suppression impossible : des paris sont déjà réglés.',
                ]);
            }

            $refunds = DB::table('bet_bets')
                ->select('user_id', DB::raw('SUM(stake) as refund'))
                ->where('match_id', $match->id)
                ->where('status', BetBetStatus::ACTIVE->value)
                ->groupBy('user_id')
                ->orderBy('user_id')
                ->get();

            foreach ($refunds as $refund) {
                $userId = (int) $refund->user_id;
                $amount = (int) $refund->refund;

                if ($amount <= 0) {
                    continue;
                }

                $wallet = Wallet::query()
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($wallet === null) {
                    Wallet::query()->create([
                        'user_id' => $userId,
                        'balance' => 0,
                    ]);

                    $wallet = Wallet::query()
                        ->where('user_id', $userId)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                $wallet->balance += $amount;
                $wallet->save();

                WalletTransaction::query()->create([
                    'user_id' => $userId,
                    'amount' => $amount,
                    'type' => WalletTransactionType::MATCH_DELETED_REFUND,
                    'reference_type' => BetMatch::class,
                    'reference_id' => $match->id,
                ]);
            }

            $refundedUsers = $refunds->count();

            $match->delete();

            return $refundedUsers;
        });
    }
}
