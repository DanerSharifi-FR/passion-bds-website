<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BetBet;
use App\Models\BetMatch;
use App\Services\AdminBettingService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminBetBetsController extends Controller
{
    public function __construct(
        private readonly AdminBettingService $adminBettingService,
    ) {
    }

    public function index(Request $request, BetMatch $match): Factory|View
    {
        $match->load('options');
        $status = $request->query('status');
        $search = trim((string) $request->query('user'));

        $betsQuery = BetBet::query()
            ->where('match_id', $match->id)
            ->with([
                'user:id,display_name,university_email',
                'option:id,label',
            ])
            ->orderByDesc('created_at');

        if ($status) {
            $betsQuery->where('status', $status);
        }

        if ($search !== '') {
            $betsQuery->whereHas('user', function ($query) use ($search) {
                $query->where('display_name', 'like', '%' . $search . '%')
                    ->orWhere('university_email', 'like', '%' . $search . '%');
            });
        }

        $bets = $betsQuery->paginate(25)->withQueryString();

        return view('betting.admin.bets.index', [
            'match' => $match,
            'bets' => $bets,
            'filters' => [
                'status' => $status,
                'user' => $search,
            ],
        ]);
    }

    public function update(Request $request, BetBet $bet): RedirectResponse
    {
        $data = $request->validate([
            'option_id' => ['required', 'integer'],
            'stake' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', 'in:ACTIVE,CANCELLED,SETTLED'],
        ], [
            'option_id.required' => 'Option obligatoire.',
            'stake.required' => 'Mise obligatoire.',
            'stake.min' => 'La mise doit être supérieure à zéro.',
            'status.in' => 'Statut invalide.',
        ]);

        try {
            $this->adminBettingService->adminUpdateBet(
                $request->user(),
                $bet->id,
                (int) $data['option_id'],
                (int) $data['stake'],
                $data['status'] ?? null
            );
        } catch (ValidationException $exception) {
            return back()->with('toast', [
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
        $hard = (bool) $request->boolean('hard');

        try {
            if ($hard) {
                $this->adminBettingService->adminDeleteBet($request->user(), $bet->id);
                $message = 'Pari supprimé définitivement.';
            } else {
                $this->adminBettingService->adminCancelBet($request->user(), $bet->id);
                $message = 'Pari annulé.';
            }
        } catch (ValidationException $exception) {
            return back()->with('toast', [
                'message' => $this->firstError($exception),
                'type' => 'error',
            ]);
        }

        return back()->with('toast', [
            'message' => $message,
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
