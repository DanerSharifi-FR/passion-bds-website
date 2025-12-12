<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class LeaderboardDemoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        $now = now();

        // Create (or reuse) a "staff" user that can appear as created_by_id for MANUAL
        $staffEmail = 'daner.sharifi@imt-atlantique.net';
        $staffId = DB::table('users')->where('university_email', $staffEmail)->value('id');

        if (!$staffId) {
            $staffId = DB::table('users')->insertGetId([
                'university_email' => $staffEmail,
                'display_name' => 'Staff Gamemaster',
                'avatar_url' => null,
                'is_active' => 1,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Demo users (idempotent-ish via deterministic emails)
        $demoCount = 60;
        $demoUserIds = [];

        for ($i = 1; $i <= $demoCount; $i++) {
            $email = sprintf('demo.player%03d@imt.net', $i);

            $existingId = DB::table('users')->where('university_email', $email)->value('id');
            if ($existingId) {
                $demoUserIds[] = (int) $existingId;
                continue;
            }

            $first = $faker->firstName();
            $last = $faker->lastName();
            $display = $first . ' ' . mb_strtoupper(mb_substr($last, 0, 1)) . '.';

            $id = DB::table('users')->insertGetId([
                'university_email' => $email,
                'display_name' => $display,
                'avatar_url' => null,
                'is_active' => 1,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $demoUserIds[] = (int) $id;
        }

        // OPTIONAL: reset demo users' transactions each run (so points don't inflate)
        DB::table('point_transactions')->whereIn('user_id', $demoUserIds)->delete();

        $reasonsChallenge = [
            "Défi du jour : QCM",
            "Défi bonus : Photo staff",
            "Défi : Question éclair",
            "Défi : Action visite",
        ];
        $reasonsAllo = [
            "Allo : P'tit Dej",
            "Allo : Livraison cafet",
            "Allo : Playlist DJ",
            "Allo : Café offert",
        ];
        $reasonsManual = [
            "Ambiance / participation",
            "Victoire mini-jeu IRL",
            "Bonus surprise",
            "Penalité (retard / triche)",
        ];

        $transactionsToInsert = [];
        $daysBack = 14;

        foreach ($demoUserIds as $userId) {
            // between 8 and 30 transactions per user
            $txCount = random_int(8, 30);

            for ($k = 0; $k < $txCount; $k++) {
                $typeRoll = random_int(1, 100);

                if ($typeRoll <= 55) {
                    $contextType = 'CHALLENGE';
                    $amount = random_int(10, 120); // earn
                    $reason = $reasonsChallenge[array_rand($reasonsChallenge)];
                    $createdById = null; // auto
                } elseif ($typeRoll <= 80) {
                    $contextType = 'ALLO';
                    $amount = -random_int(20, 180); // spend
                    $reason = $reasonsAllo[array_rand($reasonsAllo)];
                    $createdById = null;
                } else {
                    $contextType = 'MANUAL';
                    $sign = (random_int(1, 100) <= 85) ? 1 : -1;
                    $amount = $sign * random_int(5, 80);
                    if ($amount === 0) $amount = 10;
                    $reason = $reasonsManual[array_rand($reasonsManual)];
                    $createdById = $staffId;
                }

                // Spread over last 14 days
                $createdAt = Carbon::now()
                    ->subDays(random_int(0, $daysBack))
                    ->subMinutes(random_int(0, 24 * 60));

                $transactionsToInsert[] = [
                    'user_id' => $userId,
                    'amount' => $amount,
                    'reason' => $reason,
                    'context_type' => $contextType,
                    'context_id' => null,
                    'created_by_id' => $createdById,
                    'created_at' => $createdAt,
                ];
            }
        }

        // Insert in chunks (avoid huge single insert)
        foreach (array_chunk($transactionsToInsert, 2000) as $chunk) {
            DB::table('point_transactions')->insert($chunk);
        }
    }
}
