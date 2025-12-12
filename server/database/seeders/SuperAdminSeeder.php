<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $email = env('SUPER_ADMIN_UNIVERSITY_EMAIL', 'admin@etu.univ.tld');
        $displayName = env('SUPER_ADMIN_DISPLAY_NAME', 'Super Admin');
        $avatarUrl = env('SUPER_ADMIN_AVATAR_URL'); // optional

        // Upsert user by UNIQUE (university_email)
        $user = DB::table('users')->where('university_email', $email)->first();

        if ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'display_name' => $displayName,
                'avatar_url' => $avatarUrl,
                'is_active' => true,
                'updated_at' => $now,
            ]);
            $userId = $user->id;
        } else {
            DB::table('users')->insert([
                'university_email' => $email,
                'display_name' => $displayName,
                'avatar_url' => $avatarUrl,
                'is_active' => true,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $userId = DB::table('users')->where('university_email', $email)->value('id');
        }

        // Get ROLE_SUPER_ADMIN id (RoleSeeder should have created it)
        $roleId = DB::table('roles')->where('name', 'ROLE_SUPER_ADMIN')->value('id');

        if (!$roleId) {
            // safety net if someone runs this seeder alone
            DB::table('roles')->insert([
                'name' => 'ROLE_SUPER_ADMIN',
                'description' => 'All permissions + audit logs',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $roleId = DB::table('roles')->where('name', 'ROLE_SUPER_ADMIN')->value('id');
        }

        // Attach in pivot (no timestamps in user_roles)
        DB::table('user_roles')->insertOrIgnore([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }
}
