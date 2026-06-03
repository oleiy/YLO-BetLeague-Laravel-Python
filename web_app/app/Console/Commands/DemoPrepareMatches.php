<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoPrepareMatches extends Command
{
    protected $signature = 'demo:prepare-matches {datetime}';

    protected $description = 'Przygotowuje dwa mecze do demonstracji jako nadchodzące.';

    public function handle(): int
    {
        $datetime = $this->argument('datetime');

        $fixtures = DB::table('fixtures')
            ->whereNotNull('api_id')
            ->where('status', 'NS')
            ->where('is_settled', 0)
            ->orderBy('match_date')
            ->limit(2)
            ->get();

        if ($fixtures->count() < 2) {
            $this->error('Nie znaleziono dwóch meczów w bazie.');
            return self::FAILURE;
        }

        foreach ($fixtures as $fixture) {
            DB::table('fixture_statistics')
                ->where('fixture_id', $fixture->id)
                ->delete();

            DB::table('fixtures')
                ->where('id', $fixture->id)
                ->update([
                    'match_date' => $datetime,
                    'status' => 'NS',
                    'home_score' => null,
                    'away_score' => null,
                    'is_settled' => 0,
                    'updated_at' => now(),
                ]);

            DB::table('user_bets')
                ->where('fixture_id', $fixture->id)
                ->update([
                    'status' => 'pending',
                    'settled_at' => null,
                    'updated_at' => now(),
                ]);
        }

        $this->info('Przygotowano mecze demonstracyjne:');

        foreach ($fixtures as $fixture) {
            $this->line("Fixture ID: {$fixture->id}, API ID: {$fixture->api_id}, data: {$datetime}, status: NS");
        }

        return self::SUCCESS;
    }
}
