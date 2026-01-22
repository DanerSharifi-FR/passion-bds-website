<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class AdminWalletResetService
{
    /**
     * Réinitialise tous les wallets au montant demandé.
     *
     * @param int $amount
     * @return int Nombre d'utilisateurs modifiés
     */
    public function resetAllTo(int $amount): int
    {
        return DB::transaction(function () use ($amount): int {
            $users = User::query()->select('id')->orderBy('id')->get();
            $updated = 0;

            foreach ($users as $user) {
                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if ($wallet === null) {
                    $wallet = Wallet::query()->create([
                        'user_id' => $user->id,
                        'balance' => $amount,
                    ]);

                    WalletTransaction::query()->create([
                        'user_id' => $user->id,
                        'amount' => $amount,
                        'type' => WalletTransactionType::ADJUSTMENT,
                    ]);

                    $updated++;
                    continue;
                }

                $delta = $amount - (int) $wallet->balance;
                if ($delta === 0) {
                    continue;
                }

                $wallet->balance = $amount;
                $wallet->save();

                WalletTransaction::query()->create([
                    'user_id' => $user->id,
                    'amount' => $delta,
                    'type' => WalletTransactionType::ADJUSTMENT,
                ]);

                $updated++;
            }

            return $updated;
        });
    }
}
