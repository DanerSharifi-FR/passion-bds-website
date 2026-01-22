<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BetBetStatus;
use App\Enums\BetMatchStatus;
use App\Enums\WalletTransactionType;
use App\Events\BetOddsUpdated;
use App\Events\UserBetUpdated;
use App\Models\BetAdminAction;
use App\Models\BetBet;
use App\Models\BetMatch;
use App\Models\BetOption;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminBettingService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly BettingOddsService $oddsService,
    ) {
    }

    /**
     * Modifie un pari utilisateur (admin override).
     *
     * @param User $admin
     * @param int $betId
     * @param int $optionId
     * @param int $stake
     * @param string|null $status
     * @return BetBet
     *
     * @throws ValidationException
     */
    public function adminUpdateBet(User $admin, int $betId, int $optionId, int $stake, ?string $status = null): BetBet
    {
        if ($stake <= 0) {
            throw ValidationException::withMessages([
                'stake' => 'La mise doit être supérieure à zéro.',
            ]);
        }

        return DB::transaction(function () use ($admin, $betId, $optionId, $stake, $status): BetBet {
            $now = now();
            $bet = BetBet::query()->whereKey($betId)->lockForUpdate()->firstOrFail();
            $match = BetMatch::query()->whereKey($bet->match_id)->firstOrFail();

            $this->guardSettled($match, $bet);

            if ($bet->status === BetBetStatus::CANCELLED && $status !== BetBetStatus::CANCELLED->value) {
                throw ValidationException::withMessages([
                    'bet' => 'Un pari annulé ne peut pas être réactivé.',
                ]);
            }

            if ($bet->status === BetBetStatus::SETTLED && $status !== BetBetStatus::SETTLED->value) {
                throw ValidationException::withMessages([
                    'bet' => 'Un pari réglé ne peut pas être modifié.',
                ]);
            }

            if ($status === BetBetStatus::CANCELLED->value) {
                $this->adminCancelBet($admin, $betId);
                return $bet->refresh();
            }

            if ($status === BetBetStatus::SETTLED->value) {
                if ((int) $bet->option_id !== $optionId || (int) $bet->stake !== $stake) {
                    throw ValidationException::withMessages([
                        'status' => 'Impossible de modifier mise ou option sur un pari réglé.',
                    ]);
                }

                $bet->status = BetBetStatus::SETTLED;
                $bet->save();

                $this->logAdminAction($admin, $bet, 'BET_STATUS_SETTLED', [
                    'status' => BetBetStatus::SETTLED->value,
                ]);

                event(new UserBetUpdated($bet->user_id, $bet));

                return $bet->refresh();
            }

            $options = $this->lockMatchOptions($match->id);
            $newOption = $options->firstWhere('id', $optionId);

            if ($newOption === null) {
                throw ValidationException::withMessages([
                    'option' => 'Option de pari invalide pour ce match.',
                ]);
            }

            $this->lockWallet($bet->user_id);

            $oldStake = (int) $bet->stake;
            $oldOptionId = (int) $bet->option_id;
            $delta = $stake - $oldStake;

            if ($oldOptionId === $optionId) {
                if ($delta !== 0) {
                    DB::table('bet_options')
                        ->where('id', $optionId)
                        ->update([
                            'pool_total' => DB::raw('pool_total + ' . (int) $delta),
                            'updated_at' => $now,
                        ]);
                }
            } else {
                DB::table('bet_options')
                    ->where('id', $oldOptionId)
                    ->update([
                        'pool_total' => DB::raw('pool_total - ' . $oldStake),
                        'updated_at' => $now,
                    ]);

                DB::table('bet_options')
                    ->where('id', $optionId)
                    ->update([
                        'pool_total' => DB::raw('pool_total + ' . (int) $stake),
                        'updated_at' => $now,
                    ]);
            }

            if ($delta > 0) {
                $this->walletService->withdraw(
                    $bet->user_id,
                    $delta,
                    WalletTransactionType::BET_UPDATE_DIFF,
                    $bet
                );
            } elseif ($delta < 0) {
                $this->walletService->deposit(
                    $bet->user_id,
                    -$delta,
                    WalletTransactionType::BET_UPDATE_DIFF,
                    $bet
                );
            }

            $before = [
                'option_id' => (int) $bet->option_id,
                'stake' => (int) $bet->stake,
                'status' => $bet->status->value,
            ];

            $bet->fill([
                'option_id' => $optionId,
                'stake' => $stake,
                'odds_locked' => $newOption->current_odds,
                'status' => BetBetStatus::ACTIVE,
            ]);
            $bet->save();

            $this->oddsService->recomputeForMatch($match->id);
            $this->dispatchOddsUpdated($match->id);
            event(new UserBetUpdated($bet->user_id, $bet));

            $this->logAdminAction($admin, $bet, 'BET_UPDATE', [
                'before' => $before,
                'after' => [
                    'option_id' => $optionId,
                    'stake' => $stake,
                    'status' => $bet->status->value,
                ],
            ]);

            return $bet->refresh();
        });
    }

    /**
     * Annule un pari utilisateur (admin override).
     *
     * @param User $admin
     * @param int $betId
     * @return void
     *
     * @throws ValidationException
     */
    public function adminCancelBet(User $admin, int $betId): void
    {
        DB::transaction(function () use ($admin, $betId): void {
            $now = now();
            $bet = BetBet::query()->whereKey($betId)->lockForUpdate()->firstOrFail();
            $match = BetMatch::query()->whereKey($bet->match_id)->firstOrFail();

            $this->guardSettled($match, $bet);

            if ($bet->status === BetBetStatus::CANCELLED) {
                return;
            }

            if ($bet->status !== BetBetStatus::ACTIVE) {
                throw ValidationException::withMessages([
                    'bet' => 'Ce pari ne peut pas être annulé.',
                ]);
            }

            $this->lockMatchOptions($bet->match_id);
            $this->lockWallet($bet->user_id);

            DB::table('bet_options')
                ->where('id', $bet->option_id)
                ->update([
                    'pool_total' => DB::raw('pool_total - ' . (int) $bet->stake),
                    'updated_at' => $now,
                ]);

            $bet->status = BetBetStatus::CANCELLED;
            $bet->save();

            $this->walletService->deposit(
                $bet->user_id,
                (int) $bet->stake,
                WalletTransactionType::BET_CANCEL_REFUND,
                $bet
            );

            $this->oddsService->recomputeForMatch($bet->match_id);
            $this->dispatchOddsUpdated($bet->match_id);
            event(new UserBetUpdated($bet->user_id, $bet));

            $this->logAdminAction($admin, $bet, 'BET_CANCEL', [
                'status' => $bet->status->value,
            ]);
        });
    }

    /**
     * Supprime un pari (hard delete) avec reprise comptable si actif.
     *
     * @param User $admin
     * @param int $betId
     * @return void
     *
     * @throws ValidationException
     */
    public function adminDeleteBet(User $admin, int $betId): void
    {
        DB::transaction(function () use ($admin, $betId): void {
            $now = now();
            $bet = BetBet::query()->whereKey($betId)->lockForUpdate()->firstOrFail();
            $match = BetMatch::query()->whereKey($bet->match_id)->firstOrFail();

            $this->guardSettled($match, $bet);

            if ($bet->status === BetBetStatus::ACTIVE) {
                $this->lockMatchOptions($bet->match_id);
                $this->lockWallet($bet->user_id);

                DB::table('bet_options')
                    ->where('id', $bet->option_id)
                    ->update([
                        'pool_total' => DB::raw('pool_total - ' . (int) $bet->stake),
                        'updated_at' => $now,
                    ]);

                $bet->status = BetBetStatus::CANCELLED;
                $bet->save();

                $this->walletService->deposit(
                    $bet->user_id,
                    (int) $bet->stake,
                    WalletTransactionType::BET_CANCEL_REFUND,
                    $bet
                );

                $this->oddsService->recomputeForMatch($bet->match_id);
                $this->dispatchOddsUpdated($bet->match_id);
                event(new UserBetUpdated($bet->user_id, $bet));
            }

            $this->logAdminAction($admin, $bet, 'BET_DELETE', [
                'status' => $bet->status->value,
            ]);

            $bet->delete();
        });
    }

    /**
     * Verrouille les options du match.
     *
     * @param int $matchId
     * @return \Illuminate\Support\Collection<int, BetOption>
     */
    private function lockMatchOptions(int $matchId)
    {
        $options = BetOption::query()
            ->where('match_id', $matchId)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($options->isEmpty()) {
            throw ValidationException::withMessages([
                'match' => 'Aucune option de pari disponible pour ce match.',
            ]);
        }

        return $options;
    }

    /**
     * Verrouille la ligne wallet.
     *
     * @param int $userId
     * @return Wallet
     */
    private function lockWallet(int $userId): Wallet
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
     * Empêche toute modification d'un pari réglé sur match terminé.
     *
     * @param BetMatch $match
     * @param BetBet $bet
     * @return void
     */
    private function guardSettled(BetMatch $match, BetBet $bet): void
    {
        if ($match->status === BetMatchStatus::FINISHED && $bet->status === BetBetStatus::SETTLED) {
            throw ValidationException::withMessages([
                'bet' => 'Ce pari est réglé et ne peut plus être modifié.',
            ]);
        }
    }

    /**
     * Envoie l'évènement des cotes mises à jour.
     *
     * @param int $matchId
     * @return void
     */
    private function dispatchOddsUpdated(int $matchId): void
    {
        $options = DB::table('bet_options')
            ->where('match_id', $matchId)
            ->orderBy('id')
            ->get(['id', 'label', 'current_odds', 'pool_total'])
            ->map(static function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'label' => $row->label,
                    'current_odds' => (float) $row->current_odds,
                    'pool_total' => (int) $row->pool_total,
                ];
            })
            ->all();

        event(new BetOddsUpdated($matchId, $options));
    }

    /**
     * Log d'action admin.
     *
     * @param User $admin
     * @param BetBet $bet
     * @param string $action
     * @param array<string, mixed> $metadata
     * @return void
     */
    private function logAdminAction(User $admin, BetBet $bet, string $action, array $metadata = []): void
    {
        BetAdminAction::query()->create([
            'admin_id' => (int) $admin->id,
            'bet_id' => $bet->id,
            'match_id' => $bet->match_id,
            'action' => $action,
            'metadata' => $metadata,
        ]);
    }
}
