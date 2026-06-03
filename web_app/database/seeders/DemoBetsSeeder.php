<?php

namespace Database\Seeders;

use App\Models\Bet;
use App\Models\BetItem;
use App\Models\Fixture;
use App\Models\Odd;
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoBetsSeeder extends Seeder
{
    private const BETS_COUNT = 1000;

    private array $analyses = [
        'Gospodarze prezentują solidną formę i powinni wykorzystać atut własnego boiska.',
        'Statystyki obu zespołów wskazują na duży potencjał ofensywny w tym spotkaniu.',
        'Spodziewam się wyrównanego meczu, ale kurs wygląda atrakcyjnie względem ryzyka.',
        'Ostatnie mecze tych drużyn sugerują, że możemy zobaczyć kilka sytuacji bramkowych.',
        'Typ oparty głównie na średniej liczbie goli oraz aktualnej dyspozycji obu zespołów.',
        'Drużyna gości ma problemy w defensywie, dlatego ten kierunek wydaje się uzasadniony.',
        'Zakład oparty na statystykach rzutów rożnych i stylu gry obu drużyn.',
        'Obie drużyny regularnie dochodzą do sytuacji strzeleckich, dlatego typ ma sens.',
        'Forma gospodarzy w ostatnich spotkaniach wygląda stabilnie.',
        'Ten typ traktuję jako ryzykowny, ale kurs jest wart uwagi.',
    ];

    public function run(): void
    {
        $users = User::query()
            ->where('role', 'user')
            ->with('stats')
            ->get();

        if ($users->isEmpty()) {
            $this->command->error('Brak użytkowników z rolą user. Najpierw uruchom DemoUsersSeeder.');
            return;
        }

        $finishedFixtures = $this->getPlayableFixturesByStatus('FT');
        $notStartedFixtures = $this->getPlayableFixturesByStatus('NS');

        if ($finishedFixtures->isEmpty()) {
            $this->command->error('Brak meczów FT z kursami. Nie można utworzyć kuponów won/lost.');
            return;
        }

        if ($notStartedFixtures->isEmpty()) {
            $this->command->warn('Brak meczów NS z kursami. Kupony active zostaną zastąpione kuponami lost/won.');
        }

        DB::transaction(function () use ($users, $finishedFixtures, $notStartedFixtures) {
            for ($i = 1; $i <= self::BETS_COUNT; $i++) {
                $status = $this->drawBetStatus($notStartedFixtures->isNotEmpty());

                $fixture = $status === 'active'
                    ? $notStartedFixtures->random()
                    : $finishedFixtures->random();

                $itemsCount = $this->drawItemsCount();

                $odds = Odd::query()
                    ->where('fixture_id', $fixture->id)
                    ->inRandomOrder()
                    ->limit($itemsCount)
                    ->get();

                if ($odds->count() < $itemsCount) {
                    continue;
                }

                $user = $users->random();

                $stake = fake()->numberBetween(10, 150);
                $totalOdd = $this->calculateTotalOdd($odds);
                $potentialWin = round($stake * $totalOdd, 2);

                $analysis = fake()->boolean(50)
                    ? fake()->randomElement($this->analyses)
                    : null;

                $createdAt = $this->drawCreatedAt($status);

                $bet = Bet::create([
                    'user_id' => $user->id,
                    'fixture_id' => $fixture->id,
                    'total_odd' => $totalOdd,
                    'stake' => $stake,
                    'potential_win' => $potentialWin,
                    'analysis' => $analysis,
                    'is_betbuilder' => $odds->count() > 1,
                    'status' => $status,
                    'settled_at' => in_array($status, ['won', 'lost'], true)
                        ? fake()->dateTimeBetween($createdAt, 'now')
                        : null,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                ]);

                $itemStatuses = $this->buildItemStatuses($status, $odds->count());

                foreach ($odds as $index => $odd) {
                    BetItem::create([
                        'bet_id' => $bet->id,
                        'odd_id' => $odd->id,
                        'status' => $itemStatuses[$index],
                        'settled_at' => in_array($itemStatuses[$index], ['won', 'lost'], true)
                            ? $bet->settled_at
                            : null,
                        'created_at' => $createdAt,
                        'updated_at' => now(),
                    ]);
                }

                $this->updateUserStats($user->id, $status, $stake, $potentialWin);

                if ($i % 100 === 0) {
                    $this->command->info("Wygenerowano {$i} kuponów...");
                }
            }
        });

        $this->command->info('DemoBetsSeeder zakończony.');
    }

    private function getPlayableFixturesByStatus(string $status)
    {
        return Fixture::query()
            ->where('status', $status)
            ->whereHas('odds')
            ->get()
            ->filter(function (Fixture $fixture) {
                return Odd::query()
                    ->where('fixture_id', $fixture->id)
                    ->count() >= 4;
            })
            ->values();
    }

    private function drawBetStatus(bool $canCreateActive): string
    {
        $number = fake()->numberBetween(1, 100);

        if ($canCreateActive && $number <= 10) {
            return 'active';
        }

        if ($number <= 50) {
            return 'lost';
        }

        return 'won';
    }

    private function drawItemsCount(): int
    {
        $number = fake()->numberBetween(1, 100);

        if ($number <= 60) {
            return 1;
        }

        if ($number <= 85) {
            return 2;
        }

        if ($number <= 95) {
            return 3;
        }

        return 4;
    }

    private function calculateTotalOdd($odds): float
    {
        $sortedOdds = $odds
            ->sortByDesc('value')
            ->values();

        $total = 1.0;

        foreach ($sortedOdds as $index => $odd) {
            $value = (float) $odd->value;

            if ($index === 0) {
                $total *= $value;
            } else {
                $total *= 1 + (($value - 1) * 0.6);
            }
        }

        return round($total, 2);
    }

    private function buildItemStatuses(string $betStatus, int $itemsCount): array
    {
        if ($betStatus === 'active') {
            return array_fill(0, $itemsCount, 'pending');
        }

        if ($betStatus === 'won') {
            return array_fill(0, $itemsCount, 'won');
        }

        $statuses = [];

        for ($i = 0; $i < $itemsCount; $i++) {
            $statuses[] = fake()->boolean(65) ? 'lost' : 'won';
        }

        if (! in_array('lost', $statuses, true)) {
            $statuses[0] = 'lost';
        }

        return $statuses;
    }

    private function drawCreatedAt(string $status): \DateTimeInterface
    {
        if ($status === 'active') {
            return fake()->dateTimeBetween('-3 days', 'now');
        }

        return fake()->dateTimeBetween('-30 days', '-1 day');
    }

    private function updateUserStats(
        int $userId,
        string $status,
        int $stake,
        float $potentialWin
    ): void {
        $stats = UserStat::query()
            ->where('user_id', $userId)
            ->first();

        if (! $stats) {
            return;
        }

        $stats->total_bets += 1;
        $stats->balance_points -= $stake;

        if ($status === 'won') {
            $stats->won_bets += 1;
            $stats->current_streak += 1;
            $stats->best_streak = max($stats->best_streak, $stats->current_streak);
            $stats->balance_points += (int) $potentialWin;
        }

        if ($status === 'lost') {
            $stats->lost_bets += 1;
            $stats->current_streak = 0;
        }

        $settledBets = $stats->won_bets + $stats->lost_bets;

        $stats->accuracy_rate = $settledBets > 0
            ? round(($stats->won_bets / $settledBets) * 100, 2)
            : 0;

        $stats->save();
    }
}
