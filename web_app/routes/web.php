<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\MyBetsController;
use App\Http\Controllers\EarnBalanceController;
use App\Http\Controllers\Admin\AdminActionController;
use App\Http\Controllers\Admin\AdminUserBetsController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminMatchesController;



// ==========================================
// 1. Strona główna (Dashboard)
// ==========================================
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
// Szczegóły konkretnego meczu
// Trasa dla konkretnego meczu - to {id} to właśnie ID z bazy danych
Route::get('/mecze/{id}', [MatchesController::class, 'show'])->name('matches.show');
// ==========================================
// 2. Obsługa uwierzytelniania (Logowanie / Rejestracja / Wylogowanie)
// ==========================================
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Zabezpieczenie przed bezpośrednim wejściem na GET dla modali autoryzacji
// (Użytkownik jest przekierowywany z powrotem na główną stronę)
Route::get('/login', function () {
    return redirect('/');
});
Route::get('/register', function () {
    return redirect('/');
});

// ==========================================
// 3. Strona z meczami (wyświetlanie widoku i obsługa API)
// ==========================================
Route::get('/mecze', [MatchesController::class, 'index'])->name('matches');
Route::post('/api/bets/store', [BetController::class, 'store'])->middleware('auth');

// Trasa używana przez zapytania asynchroniczne (AJAX / Fetch) w pliku JavaScript (matches.js)
Route::get('/api/matches/{date}', [MatchesController::class, 'getMatchesByDate']);

// ==========================================
// 4. Społeczność
// ==========================================
Route::get('/community', [CommunityController::class, 'index'])
    ->name('community');
// ==========================================

// 5. Moje Typy
// ==========================================
Route::get('/moje-typy', [MyBetsController::class, 'index'])
    ->name('my-bets');

Route::delete('/moje-typy/{bet}/analysis', [MyBetsController::class, 'destroyAnalysis'])
    ->name('my-bets.analysis.destroy');

Route::put('/moje-typy/{bet}/analysis', [MyBetsController::class, 'updateAnalysis'])
    ->name('my-bets.analysis.update');

Route::get('/earn-balance', [
    EarnBalanceController::class,
    'index'
])->name('earn-balance');
/*
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminActionController::class, 'dashboard'])
            ->name('dashboard');

        Route::post('/import-leagues', [AdminActionController::class, 'importLeagues'])
            ->name('import-leagues');

        Route::post('/import-teams', [AdminActionController::class, 'importTeams'])
            ->name('import-teams');

        Route::post('/sync-fixture-stats', [AdminActionController::class, 'syncFixtureStats'])
            ->name('sync-fixture-stats');

        Route::post('/import-fixtures', [AdminActionController::class, 'importFixtures'])
            ->name('import-fixtures');

        Route::post('/update-csv', [AdminActionController::class, 'updateCsv'])
            ->name('update-csv');

        Route::post('/generate-odds', [AdminActionController::class, 'generateOdds'])
            ->name('generate-odds');

        Route::post('/settle-bets', [AdminActionController::class, 'settleBets'])
            ->name('settle-bets');

        Route::post('/run-scheduler', [AdminActionController::class, 'runScheduler'])
            ->name('run-scheduler');

        Route::get('/matches', [AdminMatchesController::class, 'index'])
            ->name('matches');

        Route::get('/user-bets', [AdminUserBetsController::class, 'index'])
            ->name('user-bets');
        Route::get('/users', [AdminUsersController::class, 'index'])
            ->name('users');
        Route::put('/users/{user}', [AdminUsersController::class, 'update'])
            ->name('users.update');
        Route::delete('/users/{user}', [AdminUsersController::class, 'destroy'])->name('users.destroy');
        Route::delete(
            '/bets/{bet}/analysis',
            [AdminUserBetsController::class, 'destroyAnalysis']
        )->name('bets.analysis.destroy');
        Route::post('/users/{user}/ban', [AdminUsersController::class, 'ban'])->name('users.ban');
    });
