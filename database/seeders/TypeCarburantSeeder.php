<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeCarburantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maintenant = now();

        DB::table('types_carburants')->insert([
            [
                'nom' => 'Essence sans plomb 95',
                'code' => 'SP95',
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ],
            [
                'nom' => 'Essence sans plomb 98',
                'code' => 'SP98',
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ],
            [
                'nom' => 'Diesel',
                'code' => 'DIESEL',
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ],
        ]);
    }
}
