<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BetBetStatus;
use App\Enums\BetResult;
use App\Enums\WalletTransactionType;
use App\Models\BetBet;
use App\Models\BetMatch;
use App\Models\BetOption;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BetSettlementService
{
    /**
     * Déclare un gagnant et règle les paris.
     *
     * @param User $admin
     * @param int $matchId
     * @param int $winnerOptionId
     * @param int|null $scoreA
     * @param int|null $scoreB
     * @return void
     */
    public function declareWinner(
        User $admin,
        int $matchId,
        int $winnerOptionId,
        ?int $scoreA = null,
        ?int $scoreB = null
    ): void {
        DB::transaction(function () use ($admin, $matchId, $winnerOptionId, $scoreA, $scoreB): void {
            $now = now();

            $match = BetMatch::query()
                ->whereKey($matchId)
                ->lockForUpdate()
                ->first();

            if ($match === null) {
                throw ValidationException::withMessages([
                    'match' => 'Match introuvable.',
                ]);
            }

            $options = BetOption::query()
                ->where('match_id', $match->id)
                ->orderBy('id')
                ->get();

            $winnerOption = $options->firstWhere('id', $winnerOptionId);
            if ($winnerOption === null) {
                throw ValidationException::withMessages([
                    'winner_option_id' => 'Option gagnante invalide pour ce match.',
                ]);
            }

            if ($match->winner_option_id !== null) {
                $this->undoWinnerInternal($admin, $match, $now);
            }

            $batchUuid = (string) Str::uuid();

            if ($scoreA !== null || $scoreB !== null) {
                $match->score_a = $scoreA ?? 0;
                $match->score_b = $scoreB ?? 0;
                $match->score_is_auto = false;
            } elseif ($match->score_a === null && $match->score_b === null) {
                [$autoA, $autoB] = $this->autoScoreForOption($options, $winnerOptionId);
                $match->score_a = $autoA;
                $match->score_b = $autoB;
                $match->score_is_auto = true;
            } else {
                $match->score_is_auto = false;
            }

            $bets = BetBet::query()
                ->where('match_id', $match->id)
                ->where('status', BetBetStatus::ACTIVE)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $payoutsByUser = [];

            foreach ($bets as $bet) {
                $isWinner = $bet->option_id === $winnerOptionId;
                $bet->status = BetBetStatus::SETTLED;
                $bet->result = $isWinner ? BetResult::WON : BetResult::LOST;
                $bet->settled_batch_uuid = $batchUuid;
                $bet->save();

                if ($isWinner) {
                    $payout = (int) floor($bet->stake * (float) $bet->odds_locked);
                    if ($payout > 0) {
                        $payoutsByUser[$bet->user_id][] = [
                            'bet' => $bet,
                            'amount' => $payout,
                        ];
                    }
                }
            }

            if (count($payoutsByUser) > 0) {
                $userIds = array_keys($payoutsByUser);
                sort($userIds);

                foreach ($userIds as $userId) {
                    $wallet = $this->findOrCreateLockedWallet((int) $userId);

                    $total = 0;
                    foreach ($payoutsByUser[$userId] as $payload) {
                        $total += $payload['amount'];
                    }

                    $wallet->balance += $total;
                    $wallet->save();

                    $rows = [];
                    foreach ($payoutsByUser[$userId] as $payload) {
                        /** @var BetBet $bet */
                        $bet = $payload['bet'];
                        $rows[] = [
                            'user_id' => $userId,
                            'amount' => $payload['amount'],
                            'type' => WalletTransactionType::PAYOUT->value,
                            'batch_uuid' => $batchUuid,
                            'batch_type' => 'SETTLE',
                            'reference_type' => $bet->getMorphClass(),
                            'reference_id' => $bet->getKey(),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    WalletTransaction::query()->insert($rows);
                }
            }

            $match->winner_option_id = $winnerOptionId;
            $match->settled_at = $now;
            $match->settled_by = (int) $admin->id;
            $match->settlement_version = $match->settlement_version + 1;
            $match->save();
        });
    }

    /**
     * Annule un gagnant déclaré et restaure les paris.
     *
     * @param User $admin
     * @param int $matchId
     * @return void
     */
    public function undoWinner(User $admin, int $matchId): void
    {
        DB::transaction(function () use ($admin, $matchId): void {
            $match = BetMatch::query()
                ->whereKey($matchId)
                ->lockForUpdate()
                ->first();

            if ($match === null) {
                throw ValidationException::withMessages([
                    'match' => 'Match introuvable.',
                ]);
            }

            if ($match->winner_option_id === null) {
                throw ValidationException::withMessages([
                    'winner_option_id' => 'Aucun gagnant à annuler.',
                ]);
            }

            $this->undoWinnerInternal($admin, $match, now());
        });
    }

    /**
     * @param User $admin
     * @param BetMatch $match
     * @param Carbon $now
     * @return void
     */
    private function undoWinnerInternal(User $admin, BetMatch $match, Carbon $now): void
    {
        $settledBets = BetBet::query()
            ->where('match_id', $match->id)
            ->where('status', BetBetStatus::SETTLED)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $batchUuid = (string) Str::uuid();

        $payoutsByUser = [];

        foreach ($settledBets as $bet) {
            if ($bet->result === BetResult::WON) {
                $payout = (int) floor($bet->stake * (float) $bet->odds_locked);
                if ($payout > 0) {
                    $payoutsByUser[$bet->user_id][] = [
                        'bet' => $bet,
                        'amount' => $payout,
                    ];
                }
            }
        }

        if (count($payoutsByUser) > 0) {
            $userIds = array_keys($payoutsByUser);
            sort($userIds);

            foreach ($userIds as $userId) {
                $wallet = $this->findOrCreateLockedWallet((int) $userId);

                $total = 0;
                foreach ($payoutsByUser[$userId] as $payload) {
                    $total += $payload['amount'];
                }

                if ($wallet->balance < $total) {
                    throw ValidationException::withMessages([
                        'balance' => 'Annulation impossible : solde insuffisant pour annuler les paiements.',
                    ]);
                }

                $wallet->balance -= $total;
                $wallet->save();

                $rows = [];
                foreach ($payoutsByUser[$userId] as $payload) {
                    /** @var BetBet $bet */
                    $bet = $payload['bet'];
                    $rows[] = [
                        'user_id' => $userId,
                        'amount' => -$payload['amount'],
                        'type' => WalletTransactionType::PAYOUT_UNDO->value,
                        'batch_uuid' => $batchUuid,
                        'batch_type' => 'UNDO',
                        'reference_type' => $bet->getMorphClass(),
                        'reference_id' => $bet->getKey(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                WalletTransaction::query()->insert($rows);
            }
        }

        foreach ($settledBets as $bet) {
            $bet->status = BetBetStatus::ACTIVE;
            $bet->result = null;
            $bet->settled_batch_uuid = null;
            $bet->save();
        }

        if ($match->score_is_auto) {
            $match->score_a = null;
            $match->score_b = null;
            $match->score_is_auto = false;
        }

        $match->winner_option_id = null;
        $match->settled_at = null;
        $match->settled_by = null;
        $match->settlement_version = $match->settlement_version + 1;
        $match->save();
    }

    /**
     * @param BetOption[] $options
     * @param int $winnerOptionId
     * @return array{int, int}
     */
    private function autoScoreForOption($options, int $winnerOptionId): array
    {
        $optionIds = [];
        foreach ($options as $option) {
            $optionIds[] = $option->id;
        }

        $index = array_search($winnerOptionId, $optionIds, true);

        return match ($index) {
            0 => [1, 0],
            1 => [0, 0],
            default => [0, 1],
        };
    }

    /**
     * @param int $userId
     * @return Wallet
     */
    private function findOrCreateLockedWallet(int $userId): Wallet
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
}
