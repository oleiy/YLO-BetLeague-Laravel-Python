<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Symfony\Component\Process\Process;
use App\Services\AdminStatsService;

/**
 * Główny kontroler zarządzający operacjami administracyjnymi.
 * Odpowiada za:
 * 1. Prezentację danych w panelu statystyk (Dashboard).
 * 2. Wykonywanie zewnętrznych skryptów Python (silnik analityczny).
 * 3. Integrację procesów systemowych z interfejsem użytkownika.
 */

class AdminActionController extends Controller
{
    private AdminStatsService $statsService;
    private array $python;
    private string $pythonPath;
    private string $pythonEnginePath;

    public function __construct(AdminStatsService $statsService)
    {
        $this->statsService = $statsService;

        $this->python = config('admin.python') ?? [];

        $this->pythonPath = config('admin.python_path');

        $this->pythonEnginePath = dirname(base_path()) . DIRECTORY_SEPARATOR . 'python_engine';
    }

    // =========================================
    // DASHBOARD
    // =========================================
    /**
     * Pobiera statystyki systemowe do wyświetlenia na dashboardzie administratora.
     */
    public function dashboard()
    {
        return view(
            'admin.dashboard',
            $this->statsService->getDashboardStats()
        );
    }

    /**
     * Mechanizm wykonawczy procesów Python.
     */
    private function runPython(string $scriptPath)
    {
        try {
            if (!$scriptPath || !file_exists($scriptPath)) {
                return [
                    'success' => false,
                    'output' => '',
                    'error' => "Skrypt nie istnieje: " . $scriptPath,
                ];
            }

            $env = array_merge(getenv(), [
                'PYTHONPATH' => $this->pythonEnginePath,
                'PYTHONIOENCODING' => 'utf-8',
            ]);

            $process = new Process(
                [$this->pythonPath, $scriptPath],
                $this->pythonEnginePath,
                $env
            );

            $process->setTimeout(300);
            ini_set('max_execution_time', '300');
            $process->run();

            return [
                'success' => $process->isSuccessful(),
                'output'  => $this->decodeOutput($process->getOutput()),
                'error'   => $this->decodeOutput($process->getErrorOutput()),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'output' => '',
                'error' => $e->getMessage(),
            ];
        }
    }

    // =========================================
    // OUTPUT FIX
    // =========================================
    private function decodeOutput($text)
    {
        if (!$text) return '';

        return @iconv('Windows-1250', 'UTF-8//IGNORE', $text) ?: $text;
    }

    // =========================================
    // RESPONSE
    // =========================================
    private function responseResult(array $result, string $message)
    {
        return response()->json([
            'success' => $result['success'],
            'message' => $result['success']
                ? $message
                : 'Wystąpił błąd',

            'output' => $result['success']
                ? $result['output']
                : $result['error'],
        ]);
    }

    // =========================================
    // ACTIONS
    // =========================================

    public function syncFixtureStats()
    {
        return $this->responseResult(
            $this->runPython($this->python['import_fixture_statistics'] ?? ''),
            'Statystyki meczów zaktualizowane'
        );
    }

    public function importFixtures()
    {
        return $this->responseResult(
            $this->runPython($this->python['import_fixtures'] ?? ''),
            'Mecze zostały pobrane'
        );
    }

    public function importLeagues()
    {
        return $this->responseResult(
            $this->runPython($this->python['import_leagues'] ?? ''),
            'Ligi zostały zaimportowane'
        );
    }

    public function importTeams()
    {
        return $this->responseResult(
            $this->runPython($this->python['import_teams'] ?? ''),
            'Drużyny zostały zaimportowane'
        );
    }

    public function updateCsv()
    {
        $csvResult = $this->runPython(
            $this->python['update_csv_data'] ?? ''
        );

        if (!$csvResult['success']) {
            return $this->responseResult(
                $csvResult,
                'Błąd synchronizacji CSV'
            );
        }

        $statsResult = $this->runPython(
            $this->python['stats_aggregator'] ?? ''
        );

        if (!$statsResult['success']) {
            return $this->responseResult(
                $statsResult,
                'Błąd agregacji statystyk'
            );
        }

        return $this->responseResult(
            [
                'success' => true,
                'output' =>
                $csvResult['output']
                    . "\n\n==============================\n"
                    . "GENEROWANIE STATYSTYK\n"
                    . "==============================\n\n"
                    . $statsResult['output']
            ],
            'Statystyki zostały zsynchronizowane'
        );
    }

    public function generateOdds()
    {
        return $this->responseResult(
            $this->runPython($this->python['odds_engine'] ?? ''),
            'Kursy zostały wygenerowane'
        );
    }

    public function settleBets()
    {
        return $this->responseResult(
            $this->runPython($this->python['settle_bets'] ?? ''),
            'Typy zostały zweryfikowane (kolejka sprawdzona)'
        );
    }

    // To nie działa
    /**
     ** Ręczne wywołanie schedulera.
     */
    public function runScheduler()
    {
        $artisan = base_path('artisan');

        $process = new Process(
            ['php', $artisan, 'schedule:run'],
            base_path()
        );

        $process->setTimeout(300);

        $process->run();

        return response()->json([
            'success' => $process->isSuccessful(),

            'message' => $process->isSuccessful()
                ? 'Scheduler został wykonany'
                : 'Błąd schedulera',

            'output' => $process->isSuccessful()
                ? $process->getOutput()
                : $process->getErrorOutput(),
        ]);
    }
}
