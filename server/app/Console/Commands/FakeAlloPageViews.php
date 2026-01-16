<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Allo;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Random\RandomException;

final class FakeAlloPageViews extends Command
{
    protected $signature = 'allos:fake-visits
        {--days=14 : Number of past days to generate (calendar days, includes today)}
        {--min=0 : Min unique visitors per allo per day}
        {--max=20 : Max unique visitors per allo per day}
        {--allo= : Only seed one allo_id}
        {--truncate : Wipe allo_page_views before seeding}';

    protected $description = 'Insert random authenticated unique visits (user_id) for allo slots pages over the last N days.';

    /**
     * @throws RandomException
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $min = max(0, (int) $this->option('min'));
        $max = max($min, (int) $this->option('max'));
        $onlyAlloId = $this->option('allo');

        if ($days > 365) {
            $this->warn('Clamping --days to 365.');
            $days = 365;
        }

        if ($this->option('truncate')) {
            try {
                DB::table('allo_page_views')->truncate();
            } catch (\Throwable) {
                DB::table('allo_page_views')->delete();
            }
            $this->info('allo_page_views cleared.');
        }

        $alloQuery = Allo::query()->select('id');
        if ($onlyAlloId !== null && $onlyAlloId !== '') {
            $alloQuery->where('id', (int) $onlyAlloId);
        }
        $alloIds = $alloQuery->pluck('id')->all();

        $userIds = User::query()->select('id')->pluck('id')->all();

        if (count($alloIds) === 0) {
            $this->error('No allos found (check --allo).');
            return self::FAILURE;
        }
        if (count($userIds) === 0) {
            $this->error('No users found.');
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Seeding %d day(s), %d allo(s), %d user(s). Range visitors/day/allo: %d..%d',
            $days,
            count($alloIds),
            count($userIds),
            $min,
            $max
        ));

        $now = CarbonImmutable::now();
        $rows = [];
        $inserted = 0;
        $chunkSize = 1000;

        // Includes today: days-1 back to 0
        for ($i = $days - 1; $i >= 0; $i--) {
            $dayStart = $now->startOfDay()->subDays($i);
            $dayEndExclusive = $dayStart->addDay();

            foreach ($alloIds as $alloId) {
                $n = random_int($min, $max);
                $n = min($n, count($userIds)); // can’t pick more unique users than exist

                if ($n === 0) {
                    continue;
                }

                $picked = Arr::random($userIds, $n);
                $picked = is_array($picked) ? $picked : [$picked];

                foreach ($picked as $userId) {
                    // random timestamp inside the calendar day
                    $seconds = random_int(0, 86399);
                    $viewedAt = $dayStart->addSeconds($seconds);

                    // Guard just in case
                    if ($viewedAt->lessThan($dayStart) || $viewedAt->greaterThanOrEqualTo($dayEndExclusive)) {
                        $viewedAt = $dayStart->addHours(random_int(0, 23))->addMinutes(random_int(0, 59))->addSeconds(random_int(0, 59));
                    }

                    $rows[] = [
                        'allo_id' => (int) $alloId,
                        'user_id' => (int) $userId,
                        'viewed_at' => $viewedAt->toDateTimeString(),
                    ];
                    $inserted++;

                    if (count($rows) >= $chunkSize) {
                        DB::table('allo_page_views')->insert($rows);
                        $rows = [];
                    }
                }
            }
        }

        if (count($rows) > 0) {
            DB::table('allo_page_views')->insert($rows);
        }

        $this->info("Done. Inserted rows: {$inserted}");
        return self::SUCCESS;
    }
}
