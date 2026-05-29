<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MatchService;
use App\Http\Requests\Admin\AdminIndexRequest;

/**
 * * Odpowiada za obsługę widoku listy meczów w panelu administracyjnym.
 * Wykorzystuje MatchService do pobierania przefiltrowanych danych spotkań
 * dla konkretnej daty oraz zarządza walidacją wejściową za pomocą AdminIndexRequest.
 */

class AdminMatchesController extends Controller
{
    protected MatchService $matchService;

    public function __construct(MatchService $matchService)
    {
        $this->matchService = $matchService;
    }

    public function index(
        AdminIndexRequest $request
    ) {

        $date = $request->validated('date')
            ?? now()->toDateString();

        $leagues = $this->matchService
            ->getMatchesByDate($date);

        return view('admin.matches', [
            'leagues' => $leagues,
            'date' => $date,
        ]);
    }
}
