<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BetMatchStatus;
use App\Http\Controllers\Controller;
use App\Models\BetAdminAction;
use App\Models\BetMatch;
use App\Models\BetOption;
use App\Services\AdminBetMatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AdminBetMatchController extends Controller
{
    public function __construct(
        private readonly AdminBetMatchService $adminBetMatchService,
    ) {
    }

    public function index(): Factory|View
    {
        $matches = BetMatch::query()
            ->withCount('bets')
            ->orderByDesc('match_start_at')
            ->paginate(20);

        return view('betting.admin.matches.index', [
            'matches' => $matches,
        ]);
    }

    public function create(): Factory|View
    {
        return view('betting.admin.matches.create');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'bet_open_at' => ['required', 'date'],
            'match_start_at' => ['required', 'date'],
            'match_end_at' => ['required', 'date'],
            'options' => ['required', 'array', 'size:3'],
            'options.*.label' => ['nullable', 'string', 'max:255'],
            'options.*.odds' => ['nullable', 'numeric', 'min:1.01', 'max:50'],
        ], [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'bet_open_at.required' => 'La date d’ouverture des paris est obligatoire.',
            'match_start_at.required' => 'La date de début du match est obligatoire.',
            'match_end_at.required' => 'La date de fin du match est obligatoire.',
            'options.size' => 'Il faut exactement 3 options de pari.',
            'options.*.odds.min' => 'La cote minimale est 1.01.',
            'options.*.odds.max' => 'La cote maximale est 50.00.',
        ]);

        $betOpenAt = Carbon::parse($validated['bet_open_at']);
        $matchStartAt = Carbon::parse($validated['match_start_at']);
        $matchEndAt = Carbon::parse($validated['match_end_at']);
        if ($betOpenAt->gte($matchEndAt)) {
            throw ValidationException::withMessages([
                'bet_open_at' => 'La date d’ouverture doit être avant la fin du match.',
            ]);
        }

        if ($matchStartAt->gte($matchEndAt)) {
            throw ValidationException::withMessages([
                'match_end_at' => 'La fin du match doit être après le début.',
            ]);
        }

        foreach ($validated['options'] as $index => $option) {
            $label = trim((string) ($option['label'] ?? ''));
            $odds = $option['odds'] ?? null;
            $hasLabel = $label !== '';
            $hasOdds = $odds !== null && $odds !== '';

            if ($hasLabel !== $hasOdds) {
                throw ValidationException::withMessages([
                    "options.{$index}" => 'Une option doit avoir un libellé ET une cote, ou être laissée vide.',
                ]);
            }
        }

        $match = DB::transaction(function () use ($validated, $betOpenAt, $matchStartAt, $matchEndAt): BetMatch {
            $match = BetMatch::query()->create([
                'title' => $validated['title'],
                'bet_open_at' => $betOpenAt,
                'match_start_at' => $matchStartAt,
                'match_end_at' => $matchEndAt,
                'status' => BetMatchStatus::OPEN,
                'created_by' => (int) auth()->id(),
            ]);

            foreach ($validated['options'] as $option) {
                $label = trim((string) ($option['label'] ?? ''));
                $odds = $option['odds'] ?? null;
                $isEmpty = $label === '' && ($odds === null || $odds === '');

                BetOption::query()->create([
                    'match_id' => $match->id,
                    'label' => $isEmpty ? null : $label,
                    'initial_odds' => $isEmpty ? null : $odds,
                    'current_odds' => $isEmpty ? null : $odds,
                    'pool_total' => 0,
                ]);
            }

            return $match;
        });

        return redirect()
            ->route('admin.betting.matches.show', $match)
            ->with('success', 'Match créé avec succès.');
    }

    public function show(BetMatch $match): Factory|View
    {
        $match->load(['options', 'bets', 'winnerOption']);

        return view('betting.admin.matches.show', [
            'match' => $match,
        ]);
    }

    public function edit(BetMatch $match): Factory|View
    {
        $match->load(['options' => function ($query) {
            $query->orderBy('id');
        }]);

        $hasBets = $match->bets()->exists();
        $hasPool = $match->options()->where('pool_total', '>', 0)->exists();
        $canEditOptions = !$hasBets && !$hasPool;

        return view('betting.admin.matches.edit', [
            'match' => $match,
            'canEditOptions' => $canEditOptions,
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, BetMatch $match): RedirectResponse
    {
        $hasBets = $match->bets()->exists();
        $hasPool = $match->options()->where('pool_total', '>', 0)->exists();
        $canEditOptions = !$hasBets && !$hasPool;

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'bet_open_at' => ['required', 'date'],
            'match_start_at' => ['required', 'date'],
            'match_end_at' => ['required', 'date'],
            'is_visible' => ['nullable', 'boolean'],
        ];

        if ($canEditOptions) {
            $rules['options'] = ['required', 'array', 'size:3'];
            $rules['options.*.label'] = ['nullable', 'string', 'max:255'];
            $rules['options.*.odds'] = ['nullable', 'numeric', 'min:1.01', 'max:50'];
        }

        $validated = $request->validate($rules, [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'bet_open_at.required' => 'La date d’ouverture des paris est obligatoire.',
            'match_start_at.required' => 'La date de début du match est obligatoire.',
            'match_end_at.required' => 'La date de fin du match est obligatoire.',
            'options.size' => 'Il faut exactement 3 options de pari.',
            'options.*.odds.min' => 'La cote minimale est 1.01.',
            'options.*.odds.max' => 'La cote maximale est 50.00.',
        ]);

        if (!$canEditOptions && $request->has('options')) {
            throw ValidationException::withMessages([
                'options' => 'Impossible de modifier les options/cotes : des paris existent déjà.',
            ]);
        }

        $betOpenAt = Carbon::parse($validated['bet_open_at']);
        $matchStartAt = Carbon::parse($validated['match_start_at']);
        $matchEndAt = Carbon::parse($validated['match_end_at']);

        if ($betOpenAt->gte($matchEndAt)) {
            throw ValidationException::withMessages([
                'bet_open_at' => 'La date d’ouverture doit être avant la fin du match.',
            ]);
        }

        if ($matchStartAt->gte($matchEndAt)) {
            throw ValidationException::withMessages([
                'match_end_at' => 'La fin du match doit être après le début.',
            ]);
        }

        if ($canEditOptions) {
            foreach ($validated['options'] as $index => $option) {
                $label = trim((string) ($option['label'] ?? ''));
                $odds = $option['odds'] ?? null;
                $hasLabel = $label !== '';
                $hasOdds = $odds !== null && $odds !== '';

                if ($hasLabel !== $hasOdds) {
                    throw ValidationException::withMessages([
                        "options.{$index}" => 'Une option doit avoir un libellé ET une cote, ou être laissée vide.',
                    ]);
                }
            }
        }

        DB::transaction(function () use ($match, $validated, $betOpenAt, $matchStartAt, $matchEndAt, $canEditOptions): void {
            $match->title = $validated['title'];
            $match->bet_open_at = $betOpenAt;
            $match->match_start_at = $matchStartAt;
            $match->match_end_at = $matchEndAt;
            $match->is_visible = (bool) ($validated['is_visible'] ?? false);
            $match->save();

            if ($canEditOptions) {
                $options = $match->options()->orderBy('id')->get();

                foreach ($options as $index => $option) {
                    $payload = $validated['options'][$index] ?? [];
                    $label = trim((string) ($payload['label'] ?? ''));
                    $odds = $payload['odds'] ?? null;
                    $isEmpty = $label === '' && ($odds === null || $odds === '');

                    $option->label = $isEmpty ? null : $label;
                    $option->initial_odds = $isEmpty ? null : $odds;
                    $option->current_odds = $isEmpty ? null : $odds;
                    $option->pool_total = 0;
                    $option->save();
                }
            }
        });

        return redirect()
            ->route('admin.betting.matches.show', $match)
            ->with('success', 'Match mis à jour.');
    }

    public function toggleVisibility(Request $request, BetMatch $match): RedirectResponse
    {
        $match->is_visible = !$match->is_visible;
        $match->save();

        BetAdminAction::query()->create([
            'admin_id' => (int) $request->user()->id,
            'match_id' => $match->id,
            'action' => 'MATCH_VISIBILITY_TOGGLE',
            'metadata' => [
                'is_visible' => (bool) $match->is_visible,
            ],
        ]);

        return back()->with('success', 'Visibilité mise à jour.');
    }

    public function destroy(Request $request, BetMatch $match): RedirectResponse
    {
        try {
            $refundedUsers = $this->adminBetMatchService->deleteMatchAndRefund(
                $request->user(),
                $match->id
            );
        } catch (ValidationException $exception) {
            return redirect()
                ->route('admin.betting.matches.index')
                ->with('error', $this->firstError($exception));
        }

        return redirect()
            ->route('admin.betting.matches.index')
            ->with('success', "Match supprimé. Remboursements effectués: {$refundedUsers} utilisateurs.");
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
