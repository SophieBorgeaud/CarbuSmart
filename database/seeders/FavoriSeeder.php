<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $sophieId = DB::table('users')
            ->where('email', 'sophie.test@carbusmart.local')
            ->value('id');

        $louId = DB::table('users')
            ->where('email', 'lou.test@carbusmart.local')
            ->value('id');

        DB::table('favoris')->insert([
            [
                'utilisateur_id' => $sophieId,
                'station_strapi_id' => 'demo-station-001',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'utilisateur_id' => $sophieId,
                'station_strapi_id' => 'demo-station-002',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'utilisateur_id' => $louId,
                'station_strapi_id' => 'demo-station-001',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
