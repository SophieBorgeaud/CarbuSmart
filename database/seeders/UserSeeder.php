<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([
            [
                'nom' => 'Sophie Test',
                'email' => 'sophie.test@carbusmart.local',
                'password' => Hash::make('Password123!'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nom' => 'Lou Test',
                'email' => 'lou.test@carbusmart.local',
                'password' => Hash::make('Password123!'),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
