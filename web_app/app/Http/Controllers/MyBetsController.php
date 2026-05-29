<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MyBetsService;
use App\Models\Bet;


/**
 * * Kontroler odpowiedzialny za wyświetlanie historii zakładów zalogowanego użytkownika.
 * Odpowiada za:
 * 1. Personalizację widoku: obsługa stanu zalogowania (użytkownik vs gość).
 * 2. Zaawansowane filtrowanie osobistych typów (statusy, sortowanie, filtr analiz).
 * 3. Integrację z MyBetsService.
 */

class MyBetsController extends Controller
{
    protected MyBetsService $myBetsService;

    public function __construct(MyBetsService $myBetsService)
    {
        $this->myBetsService = $myBetsService;
    }

    public function index(Request $request)
    {
        // warunek czy użytkownik jest zalogowany
        if (!Auth::check()) {

            return view('my-bets', [
                'bets' => collect(),
                'isGuest' => true,
                'status' => 'active',
                'sort' => 'date_desc',
                'analysisOnly' => false,
            ]);
        }

        $status = $request->get('status', 'active');

        $sort = $request->get('sort', 'date_desc');

        $analysisOnly = $request->boolean('analysis_only');

        $bets = $this->myBetsService->getUserBets(
            Auth::id(),
            $status,
            $sort,
            $analysisOnly
        );

        return view('my-bets', [
            'bets' => $bets,
            'isGuest' => false,
            'status' => $status,
            'sort' => $sort,
            'analysisOnly' => $analysisOnly,
        ]);
    }

    // możliwość usunięcia analizy
    public function destroyAnalysis(Bet $bet)
    {
        if ($bet->user_id !== Auth::id()) {
            abort(403);
        }

        $bet->update([
            'analysis' => null
        ]);

        return back();
    }

    // możliwość edycji analizy
    public function updateAnalysis(Request $request, Bet $bet)
    {
        if ($bet->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'analysis' => ['nullable', 'string', 'max:5000'],
        ]);

        $bet->update([
            'analysis' => $validated['analysis'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'analysis' => $bet->analysis,
            ]);
        }

        return back()->with('success', 'Analiza została zaktualizowana.');
    }
}
