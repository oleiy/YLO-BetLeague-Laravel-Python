<?php

namespace App\Http\Controllers;

use App\Models\League;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\MatchService;

/**
 * * Kontroler odpowiedzialny za prezentację oferty meczowej platformy.
 * Odpowiada za:
 * 1. Obsługę kalendarza meczów (filtrowanie po dacie).
 * 2. Dynamiczne pobieranie danych za pomocą AJAX (weryfikacja statusów: przeszłe vs przyszłe).
 * 3. Nawigację do szczegółów spotkania z automatycznym wyborem daty w kalendarzu.
 * 4. Integrację z MatchService
 */
class MatchesController extends Controller
{

    public function index(Request $request)
    {
        // Pobranie daty z requestu lub ustawienie dzisiejszej jako domyślnej
        $date = $request->get('date', Carbon::today()->toDateString());
        $leagues = $this->matchService->getMatchesByDate($date);

        return view('matches', compact('leagues', 'date'));
    }

    public function getMatchesByDate(string $date)
    {
        $selectedDate = Carbon::parse($date)->toDateString();

        $today = Carbon::today()->toDateString();

        return League::where('is_active', true)
            ->with([
                'fixtures' => function ($query) use ($selectedDate, $today) {

                    $query->whereDate('match_date', $selectedDate);

                    if ($selectedDate === $today) {

                        // bez filtra statusu
                    } elseif ($selectedDate < $today) {

                        $query->where('status', 'FT');
                    } else {

                        // przyszłe mecze — bez dodatkowego filtra
                        // bo logicznie wszystkie są NS
                    }

                    // żeby ograniczyć liczbę zapytań SQL, czyli zwiększyć wydajność generowania listy meczów
                    $query->with([
                        'homeTeam',
                        'awayTeam',
                        'league',
                        'odds',
                        'statistics'
                    ])
                        ->orderBy('match_date', 'asc');
                }
            ])
            ->orderBy('priority', 'desc')
            ->get()
            ->filter(fn($league) => $league->fixtures->count() > 0)
            ->values();
    }

    public function show($id)
    {
        // 1. Pobieramy mecz, żeby znać jego datę rozpoczęcia
        $match = \App\Models\Fixture::findOrFail($id);
        $date = $match->match_date->toDateString();

        // 2. Przekierowujemy na trasę 'matches' z datą w URL i kotwicą ID dla JS
        return redirect()->route('matches', ['date' => $date])->with('open_match', $id);
    }

    protected MatchService $matchService;

    public function __construct(MatchService $matchService)
    {
        $this->matchService = $matchService;
    }
}
