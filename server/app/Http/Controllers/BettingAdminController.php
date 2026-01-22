<?php

namespace App\Http\Controllers;

use App\Services\AdminWalletResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BettingAdminController extends Controller
{
    public function resetCredits(Request $request, AdminWalletResetService $service): RedirectResponse
    {
        $updated = $service->resetAllTo(1000);

        return redirect()
            ->route('betting.matches.index')
            ->with('toast', [
                'message' => "Crédits remis à 1000 pour {$updated} utilisateur(s).",
                'type' => 'success',
            ]);
    }
}
