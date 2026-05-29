<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fixture;
use App\Models\Bet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class UpdateFixtureStatuses extends Command
{
    protected $signature = 'fixtures:update-statuses';

    protected $description = 'Automatically updates fixtures and bets statuses';

    public function handle(): void
    {
        // LOCK – zapobiega równoległym uruchomieniom (KLUCZOWE dla SQLite) - bez tego system się zawieszał
        if (!Cache::add('fixture_status_lock', true, 20)) {
            $this->warn('Command already running. Skipping...');
            return;
        }

        try {
            $now = Carbon::now();

            /*
            |---------------------------------------------
            | 1. NS -> LIVE
            |---------------------------------------------
            */
            $liveUpdated = Fixture::where('status', 'NS')
                ->where('match_date', '<=', $now)
                ->limit(200)
                ->update([
                    'status' => 'LIVE'
                ]);

            /*
            |---------------------------------------------
            | 2. LIVE -> FT
            |---------------------------------------------
            */
            $ftUpdated = Fixture::where('status', 'LIVE')
                ->where('match_date', '<=', $now->copy()->subMinutes(125))
                ->limit(200)
                ->update([
                    'status' => 'FT'
                ]);

            /*
            |---------------------------------------------
            | 3. BETS: pending -> active
            |---------------------------------------------
            */
            $activeUpdated = Bet::where('status', 'pending')
                ->whereIn('fixture_id', function ($q) {
                    $q->select('id')
                        ->from('fixtures')
                        ->where('status', 'LIVE');
                })
                ->limit(500)
                ->update([
                    'status' => 'active'
                ]);

            /*
            |---------------------------------------------
            | 4. BETS -> settling
            |---------------------------------------------
            */
            $settlingUpdated = Bet::whereIn('status', ['pending', 'active'])
                ->whereIn('fixture_id', function ($q) {
                    $q->select('id')
                        ->from('fixtures')
                        ->where('status', 'FT');
                })
                ->limit(500)
                ->update([
                    'status' => 'settling'
                ]);

            $this->info("Done:");
            $this->info("LIVE updated: {$liveUpdated}");
            $this->info("FT updated: {$ftUpdated}");
            $this->info("ACTIVE updated: {$activeUpdated}");
            $this->info("SETTLING updated: {$settlingUpdated}");
        } finally {
            Cache::forget('fixture_status_lock');
        }
    }
}
