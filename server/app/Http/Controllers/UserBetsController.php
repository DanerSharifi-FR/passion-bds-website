<?php

namespace App\Http\Controllers;

use App\Models\BetBet;
use App\Models\BetMatch;
use App\Models\Wallet;
use App\Services\BettingService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserBetsController extends Controller
{
    public function __construct(
        private readonly BettingService $bettingService,
    ) {
    }

    public function index(): Factory|View
    {
        $bets = BetBet::query()
            ->where('user_id', (int) auth()->id())
            ->with(['match.options', 'option'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('betting.user.my-bets', [
            'bets' => $bets,
            'now' => now(),
        ]);
    }

    public function store(Request $request, BetMatch $match): RedirectResponse
    {
        $data = $request->validate([
            'option_id' => ['required', 'integer'],
            'stake' => ['required', 'integer', 'min:1'],
        ], [
            'option_id.required' => 'Veuillez choisir une option.',
            'stake.required' => 'Veuillez saisir une mise.',
            'stake.min' => 'La mise doit être supérieure à zéro.',
        ]);

        try {
            $bet = $this->bettingService->placeOrUpdateBet(
                $request->user(),
                $match->id,
                (int) $data['option_id'],
                (int) $data['stake']
            );
        } catch (ValidationException $exception) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => $this->firstError($exception),
                ], 422);
            }

            return back()
                ->withInput()
                ->with('toast', [
                    'message' => $this->firstError($exception),
                    'type' => 'error',
                ]);
        }

        if ($request->wantsJson()) {
            $wallet = Wallet::query()->where('user_id', (int) $request->user()->id)->first();
            $options = $match->options()->get(['id', 'label', 'current_odds', 'pool_total']);

            return response()->json([
                'message' => 'Pari enregistré.',
                'bet' => $bet->load('option'),
                'wallet_balance' => $wallet?->balance ?? 0,
                'options' => $options,
            ]);
        }

        return redirect()
            ->route('betting.matches.show', $match)
            ->with('toast', [
                'message' => 'Pari enregistré.',
                'type' => 'success',
            ]);
    }

    public function update(Request $request, BetBet $bet): RedirectResponse
    {
        $data = $request->validate([
            'option_id' => ['required', 'integer'],
            'stake' => ['required', 'integer', 'min:1'],
        ], [
            'option_id.required' => 'Veuillez choisir une option.',
            'stake.required' => 'Veuillez saisir une mise.',
            'stake.min' => 'La mise doit être supérieure à zéro.',
        ]);

        try {
            $this->bettingService->updateBet(
                $request->user(),
                $bet->id,
                (int) $data['option_id'],
                (int) $data['stake']
            );
        } catch (ValidationException $exception) {
            return back()
                ->withInput()
                ->with('toast', [
                    'message' => $this->firstError($exception),
                    'type' => 'error',
                ]);
        }

        return back()->with('toast', [
            'message' => 'Pari mis à jour.',
            'type' => 'success',
        ]);
    }

    public function destroy(Request $request, BetBet $bet): RedirectResponse
    {
        try {
            $this->bettingService->cancelBet($request->user(), $bet->id);
        } catch (ValidationException $exception) {
            return back()->with('toast', [
                'message' => $this->firstError($exception),
                'type' => 'error',
            ]);
        }

        return back()->with('toast', [
            'message' => 'Pari annulé.',
            'type' => 'success',
        ]);
    }

    /**
     * @param ValidationException $exception
     * @return string
     */
    private function firstError(ValidationException $exception): string
    {
        $errors = $exception->errors();
        if (count($errors) === 0) {
            return 'Une erreur est survenue.';
        }

        $first = array_values($errors)[0];
        if (is_array($first) && isset($first[0])) {
            return (string) $first[0];
        }

        return 'Une erreur est survenue.';
    }
}
