<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MatchesController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\BetController;
use App\Http\Controllers\MyBetsController;
use App\Http\Controllers\EarnBalanceController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\Admin\AdminActionController;
use App\Http\Controllers\Admin\AdminMatchesController;
use App\Http\Controllers\Admin\AdminUserBetsController;
use App\Http\Controllers\Admin\AdminUsersController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
| Trasy dostępne dla każdego użytkownika, również niezalogowanego.
| Gość może przeglądać dashboard, mecze, szczegóły meczów i społeczność.
*/

Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/mecze', [MatchesController::class, 'index'])
    ->name('matches');

Route::get('/mecze/{id}', [MatchesController::class, 'show'])
    ->name('matches.show');

Route::get('/api/matches/{date}', [MatchesController::class, 'getMatchesByDate']);

Route::get('/community', [CommunityController::class, 'index'])
    ->name('community');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
| Obsługa logowania, rejestracji i wylogowania.
| Formularze są obsługiwane przez modale, dlatego wejście GET na /login
| lub /register przekierowuje użytkownika na stronę główną.
*/

Route::post('/login', [LoginController::class, 'login'])
    ->name('login');

Route::post('/register', [RegisterController::class, 'register'])
    ->name('register');

Route::post('/logout', [LogoutController::class, 'logout'])
    ->name('logout');

Route::get('/login', fn () => redirect('/'));
Route::get('/register', fn () => redirect('/'));

/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ROUTES
|--------------------------------------------------------------------------
| Trasy dostępne tylko dla zalogowanych użytkowników.
| Obejmują składanie typów, historię kuponów, edycję analiz
| oraz system zdobywania punktów.
*/

Route::middleware('auth')->group(function () {

    Route::post('/api/bets/store', [BetController::class, 'store'])
        ->name('bets.store');

    Route::get('/moje-typy', [MyBetsController::class, 'index'])
        ->name('my-bets');

    Route::put('/moje-typy/{bet}/analysis', [MyBetsController::class, 'updateAnalysis'])
        ->name('my-bets.analysis.update');

    Route::delete('/moje-typy/{bet}/analysis', [MyBetsController::class, 'destroyAnalysis'])
        ->name('my-bets.analysis.destroy');

    Route::get('/earn-balance', [EarnBalanceController::class, 'index'])
        ->name('earn-balance');
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
| Wszystkie trasy administracyjne wymagają:
| 1. zalogowania użytkownika,
| 2. posiadania roli admin.
|
| Prefix /admin oznacza, że każda trasa zaczyna się od /admin.
| name('admin.') dodaje przedrostek do nazw tras, np. admin.dashboard.
*/

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminActionController::class, 'dashboard'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | ADMIN PYTHON ENGINE ACTIONS
        |--------------------------------------------------------------------------
        | Przyciski uruchamiające skrypty Python Engine z poziomu panelu admina.
        */

        Route::post('/import-leagues', [AdminActionController::class, 'importLeagues'])
            ->name('import-leagues');

        Route::post('/import-teams', [AdminActionController::class, 'importTeams'])
            ->name('import-teams');

        Route::post('/import-fixtures', [AdminActionController::class, 'importFixtures'])
            ->name('import-fixtures');

        Route::post('/sync-fixture-stats', [AdminActionController::class, 'syncFixtureStats'])
            ->name('sync-fixture-stats');

        Route::post('/update-csv', [AdminActionController::class, 'updateCsv'])
            ->name('update-csv');

        Route::post('/generate-odds', [AdminActionController::class, 'generateOdds'])
            ->name('generate-odds');

        Route::post('/settle-bets', [AdminActionController::class, 'settleBets'])
            ->name('settle-bets');

        Route::post('/run-scheduler', [AdminActionController::class, 'runScheduler'])
            ->name('run-scheduler');

        /*
        |--------------------------------------------------------------------------
        | ADMIN VIEWS
        |--------------------------------------------------------------------------
        */

        Route::get('/matches', [AdminMatchesController::class, 'index'])
            ->name('matches');

        Route::get('/user-bets', [AdminUserBetsController::class, 'index'])
            ->name('user-bets');

        Route::delete('/bets/{bet}/analysis', [AdminUserBetsController::class, 'destroyAnalysis'])
            ->name('bets.analysis.destroy');

        Route::get('/users', [AdminUsersController::class, 'index'])
            ->name('users');

        Route::put('/users/{user}', [AdminUsersController::class, 'update'])
            ->name('users.update');

        Route::delete('/users/{user}', [AdminUsersController::class, 'destroy'])
            ->name('users.destroy');

        Route::post('/users/{user}/ban', [AdminUsersController::class, 'ban'])
            ->name('users.ban');
    });
