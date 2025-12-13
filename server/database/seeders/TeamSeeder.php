<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {
            $poles = [
                ['name' => 'Bureau restreint', 'slug' => 'bureau-restreint', 'position' => 1],
                ['name' => 'Pôle Compet', 'slug' => 'pole-compet', 'position' => 2],
                ['name' => 'Pôle Com', 'slug' => 'pole-com', 'position' => 3],
                ['name' => 'Pôle Sponso', 'slug' => 'pole-sponso', 'position' => 4],
                ['name' => 'Pôle Sport', 'slug' => 'pole-sport', 'position' => 5],
                ['name' => 'Pôle Inté/Event', 'slug' => 'pole-inte-event', 'position' => 6],
                ['name' => 'Membres actifs', 'slug' => 'membres-actifs', 'position' => 7],
            ];
            $membersByPoleSlug = [
                'bureau-restreint' => [
                    ['full_name' => 'Tiago', 'nickname' => 'Prez', 'photo_base' => 'tiago'],
                    ['full_name' => 'Antoine', 'nickname' => 'Vice-prez', 'photo_base' => 'antoine'],
                    ['full_name' => 'Hugo', 'nickname' => 'Trez', 'photo_base' => 'hugo'],
                    ['full_name' => 'Barnabé', 'nickname' => 'Vice-trez', 'photo_base' => 'barnabe'],
                    ['full_name' => 'Arthur', 'nickname' => 'Secrétaire', 'photo_base' => 'arthur'],
                ],

                'pole-compet' => [
                    ['full_name' => 'Daner', 'nickname' => 'Space cake', 'photo_base' => 'daner'],
                    ['full_name' => 'Timothé', 'nickname' => 'Ceinture noire', 'photo_base' => 'timothe'],
                    ['full_name' => 'Gabriel', 'nickname' => 'MrOlympia', 'photo_base' => 'gabriel'],
                ],

                'pole-sponso' => [
                    ['full_name' => 'Étienne', 'nickname' => null, 'photo_base' => 'etienne'],
                    ['full_name' => 'Maxime', 'nickname' => null, 'photo_base' => 'maxime'],
                    ['full_name' => 'Théo', 'nickname' => null, 'photo_base' => 'theo'],
                ],

                'pole-sport' => [
                    ['full_name' => 'Léo', 'nickname' => null, 'photo_base' => 'leo'],
                    ['full_name' => 'Yassine', 'nickname' => null, 'photo_base' => 'yassine'],
                    ['full_name' => 'Omar', 'nickname' => null, 'photo_base' => 'omar'],
                ],

                'pole-inte-event' => [
                    ['full_name' => 'Pol', 'nickname' => null, 'photo_base' => 'pol'],
                    ['full_name' => 'Juliette', 'nickname' => null, 'photo_base' => 'juliette'],
                    ['full_name' => 'Marie', 'nickname' => null, 'photo_base' => 'marie'],
                    ['full_name' => 'Clarissa', 'nickname' => null, 'photo_base' => 'clarissa'],
                ],

                'pole-com' => [
                    ['full_name' => 'Anaïs', 'nickname' => null, 'photo_base' => 'anais'],
                    ['full_name' => 'Paul', 'nickname' => null, 'photo_base' => 'paul'],
                    ['full_name' => 'Alexandre', 'nickname' => null, 'photo_base' => 'alexandre'],
                    ['full_name' => 'Simon', 'nickname' => null, 'photo_base' => 'simon'],
                ],

                'membres-actifs' => [
                    ['full_name' => 'Mathis', 'nickname' => null, 'photo_base' => 'mathis'],
                    ['full_name' => 'Malo', 'nickname' => null, 'photo_base' => 'malo'],
                ],
            ];
            // Upsert poles by slug (keep created_at on updates).
            foreach ($poles as $pole) {
                $exists = DB::table('poles')->where('slug', $pole['slug'])->exists();

                $data = [
                    'name'        => $pole['name'],
                    'slug'        => $pole['slug'],
                    'description' => null,
                    'icon_name'   => null,
                    'position'    => $pole['position'],
                    'is_visible'  => true,
                    'updated_at'  => $now,
                ];

                if ($exists) {
                    DB::table('poles')->where('slug', $pole['slug'])->update($data);
                } else {
                    DB::table('poles')->insert($data + ['created_at' => $now]);
                }
            }

            // Map pole slugs to IDs.
            $poleIdsBySlug = DB::table('poles')
                ->whereIn('slug', array_map(fn ($p) => $p['slug'], $poles))
                ->pluck('id', 'slug')
                ->all();

            // Make seeder idempotent: delete members for these poles, then reinsert.
            DB::table('team_members')->whereIn('pole_id', array_values($poleIdsBySlug))->delete();

            $rows = [];

            foreach ($membersByPoleSlug as $poleSlug => $members) {
                $poleId = $poleIdsBySlug[$poleSlug] ?? null;
                if (!$poleId) {
                    throw new \RuntimeException("Missing pole_id for slug: {$poleSlug}");
                }

                $position = 0;

                foreach ($members as $member) {
                    $position++;

                    $rows[] = [
                        'pole_id'       => $poleId,
                        'full_name'     => $member['full_name'],
                        'nickname'      => $member['nickname'],
                        'bio'           => null,
                        'photo_url'     => '/assets/members/' . $member['photo_base'] . '.jpg',
                        'instagram_url' => null,
                        'position'      => $position,
                        'is_visible'    => true,
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }

            DB::table('team_members')->insert($rows);
        });
    }
}
