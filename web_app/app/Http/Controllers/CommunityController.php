<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommunityFilterRequest;
use App\Services\CommunityService;

/**
 * * Kontroler obsługujący moduł społecznościowy platformy.
 * Odpowiada za prezentację publicznych typów użytkowników oraz rankingów.
 * Wykorzystuje CommunityService do agregacji danych statystycznych, co pozwala na
 * dynamiczne filtrowanie i sortowanie aktywności społeczności w czasie rzeczywistym.
 */

class CommunityController extends Controller
{
    protected CommunityService $communityService;

    public function __construct(
        CommunityService $communityService
    ) {
        $this->communityService = $communityService;
    }

    public function index(
        CommunityFilterRequest $request
    ) {

        $date = (string) request(
            'date',
            now()->toDateString()
        );

        $sort = (string) request(
            'sort',
            'success_rate'
        );

        $analysisOnly = request()->boolean(
            'analysis_only'
        );

        $bets = $this->communityService
            ->getCommunityBets(
                $date,
                $sort,
                $analysisOnly
            );

        return view('community', [

            'bets' => $bets,

            'date' => $date,

            'sort' => $sort,

            'analysisOnly' => $analysisOnly,

            'weeklyRanking' => $this->communityService
                ->getWeeklyRanking(),

            'monthlyRanking' => $this->communityService
                ->getMonthlyRanking(),

            'globalRanking' => $this->communityService
                ->getGlobalRanking(),

            'highestOddsRanking' => $this->communityService
                ->getHighestOddsRanking(),
        ]);
    }
}
