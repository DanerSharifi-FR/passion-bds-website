<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BetBetStatus;
use App\Enums\BetMatchStatus;
use App\Enums\WalletTransactionType;
use App\Events\BetOddsUpdated;
use App\Events\UserBetUpdated;
use App\Models\BetBet;
use App\Models\BetMatch;
use App\Models\BetOption;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BettingService
{
    public function __construct(
        private readonly WalletService $walletService,
        private readonly BettingOddsService $oddsService,
    ) {
    }

    /**
     * Place un pari.
     *
     * @param User $user
     * @param int $matchId
     * @param int $optionId
     * @param int $stake
     * @return BetBet
     *
     * @throws ValidationException
     */
    public function placeBet(User $user, int $matchId, int $optionId, int $stake): BetBet
    {
        if ($stake <= 0) {
            throw ValidationException::withMessages([
                'stake' => 'La mise doit être supérieure à zéro.',
            ]);
        }

        return DB::transaction(function () use ($user, $matchId, $optionId, $stake): BetBet {
            $now = now();
            $match = BetMatch::query()->whereKey($matchId)->firstOrFail();
            $this->ensureMatchOpen($match, $now);

            $options = $this->lockMatchOptions($matchId);
            $option = $options->firstWhere('id', $optionId);

            if ($option === null) {
                throw ValidationException::withMessages([
                    'option' => 'Option de pari invalide pour ce match.',
                ]);
            }

            $this->lockWallet($user->id);

            $bet = BetBet::query()->create([
                'match_id' => $matchId,
                'option_id' => $optionId,
                'user_id' => $user->id,
                'stake' => $stake,
                'odds_locked' => $option->current_odds,
                'status' => BetBetStatus::ACTIVE,
                'editable_until' => $now->copy()->addSeconds((int) config('betting.edit_window_seconds')),
            ]);

            DB::table('bet_options')
                ->where('id', $optionId)
                ->update([
                    'pool_total' => DB::raw('pool_total + ' . (int) $stake),
                    'updated_at' => $now,
                ]);

            $this->walletService->withdraw(
                $user->id,
                $stake,
                WalletTransactionType::BET_PLACE,
                $bet
            );

            $this->oddsService->recomputeForMatch($matchId);

            $this->dispatchOddsUpdated($matchId);
            event(new UserBetUpdated($user->id, $bet));

            return $bet->refresh();
        });
    }

    /**
     * Place un pari ou met à jour le pari actif existant.
     *
     * @param User $user
     * @param int $matchId
     * @param int $optionId
     * @param int $stake
     * @return BetBet
     *
     * @throws ValidationException
     */
    public function placeOrUpdateBet(User $user, int $matchId, int $optionId, int $stake): BetBet
    {
        if ($stake <= 0) {
            throw ValidationException::withMessages([
                'stake' => 'La mise doit être supérieure à zéro.',
            ]);
        }

        return DB::transaction(function () use ($user, $matchId, $optionId, $stake): BetBet {
            $now = now();
            $match = BetMatch::query()
                ->whereKey($matchId)
                ->lockForUpdate()
                ->firstOrFail();
            $this->ensureMatchOpen($match, $now);

            $options = $this->lockMatchOptions($matchId);
            $option = $options->firstWhere('id', $optionId);

            if ($option === null) {
                throw ValidationException::withMessages([
                    'option' => 'Option de pari invalide pour ce match.',
                ]);
            }

            $this->lockWallet($user->id);

            $existingBet = BetBet::query()
                ->where('match_id', $matchId)
                ->where('user_id', $user->id)
                ->where('status', BetBetStatus::ACTIVE)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($existingBet === null) {
                $bet = BetBet::query()->create([
                    'match_id' => $matchId,
                    'option_id' => $optionId,
                    'user_id' => $user->id,
                    'stake' => $stake,
                    'odds_locked' => $option->current_odds,
                    'status' => BetBetStatus::ACTIVE,
                    'editable_until' => $now->copy()->addSeconds((int) config('betting.edit_window_seconds')),
                ]);

                DB::table('bet_options')
                    ->where('id', $optionId)
                    ->update([
                        'pool_total' => DB::raw('pool_total + ' . (int) $stake),
                        'updated_at' => $now,
                    ]);

                $this->walletService->withdraw(
                    $user->id,
                    $stake,
                    WalletTransactionType::BET_PLACE,
                    $bet
                );

                $this->oddsService->recomputeForMatch($matchId);
                $this->dispatchOddsUpdated($matchId);
                event(new UserBetUpdated($user->id, $bet));

                return $bet->refresh();
            }

            if ($now->gt($existingBet->editable_until)) {
                throw ValidationException::withMessages([
                    'bet' => 'Vous avez déjà un pari sur ce match et la fenêtre de modification est terminée.',
                ]);
            }

            $oldStake = (int) $existingBet->stake;
            $oldOptionId = (int) $existingBet->option_id;
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
                    $user->id,
                    $delta,
                    WalletTransactionType::BET_UPDATE_DIFF,
                    $existingBet
                );
            } elseif ($delta < 0) {
                $this->walletService->deposit(
                    $user->id,
                    -$delta,
                    WalletTransactionType::BET_UPDATE_DIFF,
                    $existingBet
                );
            }

            $existingBet->fill([
                'option_id' => $optionId,
                'stake' => $stake,
                'odds_locked' => $option->current_odds,
                'editable_until' => $now->copy()->addSeconds((int) config('betting.edit_window_seconds')),
            ]);
            $existingBet->save();

            $this->oddsService->recomputeForMatch($matchId);
            $this->dispatchOddsUpdated($matchId);
            event(new UserBetUpdated($user->id, $existingBet));

            return $existingBet->refresh();
        });
    }

    /**
     * Modifie un pari existant.
     *
     * @param User $user
     * @param int $betId
     * @param int $optionId
     * @param int $stake
     * @return BetBet
     *
     * @throws ValidationException
     */
    public function updateBet(User $user, int $betId, int $optionId, int $stake): BetBet
    {
        if ($stake <= 0) {
            throw ValidationException::withMessages([
                'stake' => 'La mise doit être supérieure à zéro.',
            ]);
        }

        return DB::transaction(function () use ($user, $betId, $optionId, $stake): BetBet {
            $now = now();
            $bet = BetBet::query()
                ->whereKey($betId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureBetEditable($user, $bet, $now);

            $match = BetMatch::query()->whereKey($bet->match_id)->firstOrFail();
            $this->ensureMatchOpen($match, $now);

            $options = $this->lockMatchOptions($match->id);
            $newOption = $options->firstWhere('id', $optionId);

            if ($newOption === null) {
                throw ValidationException::withMessages([
                    'option' => 'Option de pari invalide pour ce match.',
                ]);
            }

            $this->lockWallet($user->id);

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
                    $user->id,
                    $delta,
                    WalletTransactionType::BET_UPDATE_DIFF,
                    $bet
                );
            } elseif ($delta < 0) {
                $this->walletService->deposit(
                    $user->id,
                    -$delta,
                    WalletTransactionType::BET_UPDATE_DIFF,
                    $bet
                );
            }

            $bet->fill([
                'option_id' => $optionId,
                'stake' => $stake,
                'odds_locked' => $newOption->current_odds,
                'editable_until' => $now->copy()->addSeconds((int) config('betting.edit_window_seconds')),
            ]);
            $bet->save();

            $this->oddsService->recomputeForMatch($match->id);

            $this->dispatchOddsUpdated($match->id);
            event(new UserBetUpdated($user->id, $bet));

            return $bet->refresh();
        });
    }

    /**
     * Annule un pari existant.
     *
     * @param User $user
     * @param int $betId
     * @return void
     *
     * @throws ValidationException
     */
    public function cancelBet(User $user, int $betId): void
    {
        DB::transaction(function () use ($user, $betId): void {
            $now = now();
            $bet = BetBet::query()
                ->whereKey($betId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->ensureBetEditable($user, $bet, $now);

            $this->lockMatchOptions($bet->match_id);
            $this->lockWallet($user->id);

            DB::table('bet_options')
                ->where('id', $bet->option_id)
                ->update([
                    'pool_total' => DB::raw('pool_total - ' . (int) $bet->stake),
                    'updated_at' => $now,
                ]);

            $bet->status = BetBetStatus::CANCELLED;
            $bet->save();

            $this->walletService->deposit(
                $user->id,
                (int) $bet->stake,
                WalletTransactionType::BET_CANCEL_REFUND,
                $bet
            );

            $this->oddsService->recomputeForMatch($bet->match_id);

            $this->dispatchOddsUpdated($bet->match_id);
            event(new UserBetUpdated($user->id, $bet));
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
     * Vérifie si un match est ouvert aux paris.
     *
     * @param BetMatch $match
     * @param \Illuminate\Support\Carbon $now
     * @return void
     */
    private function ensureMatchOpen(BetMatch $match, Carbon $now): void
    {
        if (!$match->isUserVisible()) {
            throw ValidationException::withMessages([
                'match' => 'Ce match est indisponible.',
            ]);
        }

        if ($match->status !== BetMatchStatus::OPEN) {
            throw ValidationException::withMessages([
                'match' => 'Les paris ne sont pas ouverts pour ce match.',
            ]);
        }

        if (!$match->isBettingWindowOpen($now)) {
            throw ValidationException::withMessages([
                'match' => 'La fenêtre de pari est fermée pour ce match.',
            ]);
        }
    }

    /**
     * Vérifie qu'un pari est modifiable.
     *
     * @param User $user
     * @param BetBet $bet
     * @param \Illuminate\Support\Carbon $now
     * @return void
     */
    private function ensureBetEditable(User $user, BetBet $bet, Carbon $now): void
    {
        if ((int) $bet->user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'bet' => 'Vous ne pouvez pas modifier ce pari.',
            ]);
        }

        if ($bet->status !== BetBetStatus::ACTIVE) {
            throw ValidationException::withMessages([
                'bet' => 'Ce pari ne peut plus être modifié.',
            ]);
        }

        if ($now->gt($bet->editable_until)) {
            throw ValidationException::withMessages([
                'bet' => 'La fenêtre de modification est expirée.',
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
}
