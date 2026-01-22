<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\WalletTransactionType;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Console\Command;

final class BettingBackfillWallets extends Command
{
    protected $signature = 'betting:backfill-wallets';

    protected $description = 'Crée les wallets manquants et crédite les utilisateurs avec le crédit initial.';

    public function __construct(
        private readonly WalletService $walletService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $initialCredits = (int) config('betting.initial_credits');

        $users = User::query()
            ->whereNotIn('id', Wallet::query()->select('user_id'))
            ->get(['id']);

        $created = 0;

        foreach ($users as $user) {
            $this->walletService->deposit(
                (int) $user->id,
                $initialCredits,
                WalletTransactionType::INITIAL
            );
            $created++;
        }

        $this->info(sprintf('Wallets créés et crédités: %d', $created));

        return self::SUCCESS;
    }
}
