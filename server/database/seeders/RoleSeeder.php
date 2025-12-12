<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'ROLE_GAMEMASTER',  'description' => 'Points, challenges, allos'],
            ['name' => 'ROLE_BLOGGER',     'description' => 'Events + galleries'],
            ['name' => 'ROLE_TEAM',        'description' => 'Team + pôles'],
            ['name' => 'ROLE_SHOP',        'description' => 'Fake shop catalog'],
            ['name' => 'ROLE_SUPER_ADMIN', 'description' => 'All permissions + audit logs'],
        ];

        foreach ($roles as $r) {
            $existingId = DB::table('roles')->where('name', $r['name'])->value('id');

            if ($existingId) {
                DB::table('roles')->where('id', $existingId)->update([
                    'description' => $r['description'],
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('roles')->insert([
                    'name' => $r['name'],
                    'description' => $r['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
