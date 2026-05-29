<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CommunityService;
use App\Http\Requests\Admin\AdminIndexRequest;
use App\Models\Bet;

/**
 * * Odpowiada za zarządzanie i podgląd zakładów postawionych przez społeczność graczy w panelu administracyjnym.
 * Wykorzystuje CommunityService do agregacji i pobierania typów użytkowników dla konkretnej daty,
 * zapewniając administratorowi wgląd w aktywność społeczności.
 */

class AdminUserBetsController extends Controller
{
    protected CommunityService $communityService;

    public function __construct(
        CommunityService $communityService
    ) {
        $this->communityService = $communityService;
    }

    public function destroyAnalysis(Bet $bet)
    {
        $bet->update([
            'analysis' => null
        ]);

        return back()->with(
            'success',
            'Analiza została usunięta.'
        );
    }

    public function index(
        AdminIndexRequest $request
    ) {

        $date = $request->validated('date')
            ?? now()->toDateString();

        $userId = $request->get('user_id');

        $bets = $this->communityService
            ->getCommunityBets(
                $date,
                'success_rate',
                false,
                $userId
            );

        $users = \App\Models\User::orderBy('username')
            ->get();

        return view(
            'admin.user-bets',
            [
                'bets' => $bets,
                'date' => $date,
                'users' => $users,
                'selectedUser' => $userId,
            ]
        );
    }
}
