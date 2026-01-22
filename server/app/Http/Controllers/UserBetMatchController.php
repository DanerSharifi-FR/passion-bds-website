<?php

namespace App\Http\Controllers;

use App\Enums\BetBetStatus;
use App\Models\BetBet;
use App\Models\BetMatch;
use App\Services\WalletService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class UserBetMatchController extends Controller
{
    public function __construct(
        private readonly WalletService $walletService,
    ) {
    }

    public function index(): Factory|View
    {
        $matches = BetMatch::query()
            ->with('options')
            ->where('is_visible', true)
            ->orderBy('match_start_at')
            ->paginate(10);

        $wallet = $this->walletService->getOrCreate((int) auth()->id());

        return view('betting.user.index', [
            'matches' => $matches,
            'wallet' => $wallet,
            'now' => now(),
        ]);
    }

    public function show(BetMatch $match): Factory|View
    {
        if (!$match->isUserVisible()) {
            abort(404);
        }

        $match->load('options');
        $wallet = $this->walletService->getOrCreate((int) auth()->id());
        $activeBet = BetBet::query()
            ->where('match_id', $match->id)
            ->where('user_id', (int) auth()->id())
            ->where('status', BetBetStatus::ACTIVE)
            ->orderByDesc('created_at')
            ->first();
        if ($activeBet) {
            $activeBet->load('option');
        }
        $now = now();

        return view('betting.user.show', [
            'match' => $match,
            'wallet' => $wallet,
            'activeBet' => $activeBet,
            'now' => $now,
        ]);
    }
}
