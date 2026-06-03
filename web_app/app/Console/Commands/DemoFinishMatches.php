<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoFinishMatches extends Command
{
    protected $signature = 'demo:finish-matches {fixture_ids*}';

    protected $description = 'Ustawia wskazane mecze demonstracyjne jako zakończone.';

    public function handle(): int
    {
        $fixtureIds = $this->argument('fixture_ids');

        if (count($fixtureIds) !== 2) {
            $this->error('Podaj dokładnie dwa ID meczów, np. php artisan demo:finish-matches 36 37');
            return self::FAILURE;
        }

        $fixtures = DB::table('fixtures')
            ->whereIn('id', $fixtureIds)
            ->whereIn('status', ['NS', 'LIVE'])
            ->where('is_settled', 0)
            ->get();

        if ($fixtures->count() !== 2) {
            $this->error('Nie znaleziono dokładnie dwóch meczów ze statusem NS i is_settled = 0.');

            $found = DB::table('fixtures')
                ->whereIn('id', $fixtureIds)
                ->select('id', 'api_id', 'match_date', 'status', 'is_settled')
                ->get();

            $this->line('Znalezione rekordy:');

            foreach ($found as $fixture) {
                $this->line(
                    "Fixture ID: {$fixture->id}, API ID: {$fixture->api_id}, " .
                        "data: {$fixture->match_date}, status: {$fixture->status}, " .
                        "is_settled: {$fixture->is_settled}"
                );
            }

            return self::FAILURE;
        }

        foreach ($fixtures as $fixture) {
            DB::table('fixtures')
                ->where('id', $fixture->id)
                ->update([
                    'status' => 'FT',
                    'updated_at' => now(),
                ]);
        }

        $this->info('Ustawiono mecze jako FT:');

        foreach ($fixtures as $fixture) {
            $this->error('Nie znaleziono dokładnie dwóch meczów ze statusem NS/LIVE i is_settled = 0.');
        }

        return self::SUCCESS;
    }
}
