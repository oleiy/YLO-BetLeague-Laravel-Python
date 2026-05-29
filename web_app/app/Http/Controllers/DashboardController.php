<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use Illuminate\Http\Request;
use App\Services\MatchService;

/**
 * * Kontroler odpowiedzialny za wyświetlanie głównego panelu użytkownika.
 */
class DashboardController extends Controller
{
    /**
     * Pobiera przygotowane przez MatchService dane o meczach i przekazuje je do widoku.
     */
    public function index()
    {
        $matches = $this->matchService->getDashboardMatches();

        return view('dashboard', compact('matches'));
    }

    protected MatchService $matchService;

    public function __construct(MatchService $matchService)
    {
        $this->matchService = $matchService;
    }
}
