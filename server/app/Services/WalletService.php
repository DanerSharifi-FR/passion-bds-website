<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /**
     * Récupère le wallet d'un utilisateur, ou le crée s'il n'existe pas.
     *
     * @param int $userId
     * @return Wallet
     */
    public function getOrCreate(int $userId): Wallet
    {
        return DB::transaction(function () use ($userId): Wallet {
            return $this->findOrCreateLocked($userId);
        });
    }

    /**
     * Dépôt de crédits (montant positif).
     *
     * @param int $userId
     * @param int $amount
     * @param WalletTransactionType $type
     * @param Model|null $ref
     * @return void
     */
    public function deposit(int $userId, int $amount, WalletTransactionType $type, ?Model $ref = null): void
    {
        DB::transaction(function () use ($userId, $amount, $type, $ref): void {
            $wallet = $this->findOrCreateLocked($userId);

            $wallet->balance += $amount;
            $wallet->save();

            $this->createTransaction($userId, $amount, $type, $ref);
        });
    }

    /**
     * Retrait de crédits (montant positif).
     *
     * @param int $userId
     * @param int $amount
     * @param WalletTransactionType $type
     * @param Model|null $ref
     * @return void
     *
     * @throws ValidationException
     */
    public function withdraw(int $userId, int $amount, WalletTransactionType $type, ?Model $ref = null): void
    {
        DB::transaction(function () use ($userId, $amount, $type, $ref): void {
            $wallet = $this->findOrCreateLocked($userId);

            if ($wallet->balance < $amount) {
                throw ValidationException::withMessages([
                    'balance' => 'Solde insuffisant pour effectuer ce retrait.',
                ]);
            }

            $wallet->balance -= $amount;
            $wallet->save();

            $this->createTransaction($userId, -$amount, $type, $ref);
        });
    }

    /**
     * Récupère le wallet en verrouillant la ligne.
     *
     * @param int $userId
     * @return Wallet
     */
    private function findOrCreateLocked(int $userId): Wallet
    {
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

        return $wallet;
    }

    /**
     * Insère une ligne de ledger pour le wallet.
     *
     * @param int $userId
     * @param int $amount
     * @param WalletTransactionType $type
     * @param Model|null $ref
     * @return void
     */
    private function createTransaction(int $userId, int $amount, WalletTransactionType $type, ?Model $ref = null): void
    {
        WalletTransaction::query()->create([
            'user_id' => $userId,
            'amount' => $amount,
            'type' => $type,
            'reference_type' => $ref?->getMorphClass(),
            'reference_id' => $ref?->getKey(),
        ]);
    }
}
