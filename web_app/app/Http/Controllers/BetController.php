<?php

namespace App\Http\Controllers;

use App\Http\Requests\BetStoreRequest;
use Illuminate\Support\Facades\Auth;
use App\Services\BetService;

/**
 * * Kontroler obsługujący postawienie zakładów przez użytkowników.
 * Odpowiada za:
 * 1. Walidację danych wejściowych zakładu za pomocą BetStoreRequest.
 * 2. Przekazywanie logiki biznesowej do warstwy serwisowej (BetService).
 */

class BetController extends Controller
{
    public function store(BetStoreRequest $request)
    {
        $validated = $request->validated();

        $result = $this->betService->placeBets(
            Auth::user(),
            $validated['bets']
        );

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'new_balance' => $result['new_balance'] ?? null
        ], $result['status']);
    }
    protected BetService $betService;

    public function __construct(BetService $betService)
    {
        $this->betService = $betService;
    }
}
