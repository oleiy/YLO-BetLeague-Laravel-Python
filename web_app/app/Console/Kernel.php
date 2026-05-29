<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Kernel konsolowy Laravela.
 * Odpowiada za definiowanie harmonogramu zadań (Task Scheduling)
 * oraz rejestrację dostępnych komend konsolowych.
 */

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Automatyzacja komendy UpdateFixtureStatuses
        $schedule->command('fixtures:update-statuses')
            ->everyMinute() // co minute sprawdź czy istnieje mecz/typ który wymaga aktualizacji statusu
            ->withoutOverlapping()  // chroni przed duplikacją aktualizacji (czeka aż się skończy komenda)
            ->runInBackground()  // nie blokuje schedulera
            ->onOneServer();  // tu bez znaczenia ale jakby było więcej serwerów to żeby tylko jeden wykonywał taska
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
