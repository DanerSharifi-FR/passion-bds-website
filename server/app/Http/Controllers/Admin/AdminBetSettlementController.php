<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BetMatch;
use App\Services\BetSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminBetSettlementController extends Controller
{
    public function __construct(
        private readonly BetSettlementService $betSettlementService,
    ) {
    }

    public function declareWinner(Request $request, BetMatch $match): RedirectResponse
    {
        $validated = $request->validate([
            'winner_option_id' => ['required', 'integer'],
            'score_a' => ['nullable', 'integer', 'min:0', 'required_with:score_b'],
            'score_b' => ['nullable', 'integer', 'min:0', 'required_with:score_a'],
        ], [
            'winner_option_id.required' => 'Veuillez sélectionner un gagnant.',
            'score_a.integer' => 'Le score A doit être un entier.',
            'score_b.integer' => 'Le score B doit être un entier.',
            'score_a.required_with' => 'Le score A est obligatoire si le score B est renseigné.',
            'score_b.required_with' => 'Le score B est obligatoire si le score A est renseigné.',
        ]);

        try {
            $this->betSettlementService->declareWinner(
                $request->user(),
                $match->id,
                (int) $validated['winner_option_id'],
                $validated['score_a'] ?? null,
                $validated['score_b'] ?? null
            );
        } catch (ValidationException $exception) {
            return back()->with('error', $this->firstError($exception));
        }

        return back()->with('success', 'Gagnant déclaré. Paris réglés.');
    }

    public function undoWinner(Request $request, BetMatch $match): RedirectResponse
    {
        try {
            $this->betSettlementService->undoWinner($request->user(), $match->id);
        } catch (ValidationException $exception) {
            return back()->with('error', $this->firstError($exception));
        }

        return back()->with('success', 'Déclaration annulée. Paiements annulés.');
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
