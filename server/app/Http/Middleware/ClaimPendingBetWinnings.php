<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\BetBetStatus;
use App\Enums\BetResult;
use App\Enums\WalletTransactionType;
use App\Models\BetBet;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ClaimPendingBetWinnings
{
    /**
     * @param Request $request
     * @param Closure(Request): Response $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        $session = $request->session();
        $lastClaim = $session->get('betting_last_claim_at');
        if (is_string($lastClaim)) {
            $lastClaimAt = Carbon::parse($lastClaim);
            if ($lastClaimAt->gt(now()->subSeconds(30))) {
                return $next($request);
            }
        }

        $userId = (int) $user->id;
        $morphClass = (new BetBet())->getMorphClass();
        $now = now();

        DB::transaction(function () use ($userId, $morphClass, $now): void {
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

            $bets = BetBet::query()
                ->where('bet_bets.user_id', $userId)
                ->where('bet_bets.status', BetBetStatus::SETTLED)
                ->where('bet_bets.result', BetResult::WON)
                ->leftJoin('wallet_transactions', function ($join) use ($userId, $morphClass): void {
                    $join->on('wallet_transactions.reference_id', '=', 'bet_bets.id')
                        ->where('wallet_transactions.reference_type', $morphClass)
                        ->where('wallet_transactions.user_id', $userId)
                        ->where('wallet_transactions.type', WalletTransactionType::PAYOUT->value);
                })
                ->whereNull('wallet_transactions.id')
                ->orderBy('bet_bets.id')
                ->limit(200)
                ->lockForUpdate()
                ->get(['bet_bets.*']);

            if ($bets->isEmpty()) {
                return;
            }

            $total = 0;
            $transactions = [];

            foreach ($bets as $bet) {
                $alreadyPaid = WalletTransaction::query()
                    ->where('user_id', $userId)
                    ->where('reference_type', $morphClass)
                    ->where('reference_id', $bet->id)
                    ->where('type', WalletTransactionType::PAYOUT->value)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyPaid) {
                    continue;
                }

                $payout = (int) floor($bet->stake * (float) $bet->odds_locked);
                if ($payout <= 0) {
                    continue;
                }

                $total += $payout;
                $transactions[] = [
                    'user_id' => $userId,
                    'amount' => $payout,
                    'type' => WalletTransactionType::PAYOUT->value,
                    'reference_type' => $morphClass,
                    'reference_id' => $bet->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($total > 0) {
                $wallet->balance += $total;
                $wallet->save();
            }

            if (count($transactions) > 0) {
                WalletTransaction::query()->insert($transactions);
            }
        });

        $session->put('betting_last_claim_at', $now->toIso8601String());

        return $next($request);
    }
}
