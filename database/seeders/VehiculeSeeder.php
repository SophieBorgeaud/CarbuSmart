<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculeSeeder extends Seeder
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

        $sp95Id = DB::table('types_carburants')
            ->where('code', 'SP95')
            ->value('id');

        $sp98Id = DB::table('types_carburants')
            ->where('code', 'SP98')
            ->value('id');

        $dieselId = DB::table('types_carburants')
            ->where('code', 'DIESEL')
            ->value('id');

        DB::table('vehicules')->insert([
            [
                'utilisateur_id' => $sophieId,
                'type_carburant_id' => $sp95Id,
                'nom_vehicule' => 'Ma Yaris',
                'conso_moyenne' => 5.40,
                'marque' => 'Toyota',
                'modele' => 'Yaris',
                'type_vehicule' => 'Citadine',
                'num_plaque' => 'JU 12345',
                'est_defaut' => true,
                'est_actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'utilisateur_id' => $sophieId,
                'type_carburant_id' => $dieselId,
                'nom_vehicule' => 'Ancienne Golf',
                'conso_moyenne' => 5.10,
                'marque' => 'Volkswagen',
                'modele' => 'Golf',
                'type_vehicule' => 'Berline',
                'num_plaque' => 'JU 54321',
                'est_defaut' => false,
                'est_actif' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'utilisateur_id' => $louId,
                'type_carburant_id' => $sp98Id,
                'nom_vehicule' => 'Ma BMW',
                'conso_moyenne' => 7.20,
                'marque' => 'BMW',
                'modele' => '320i',
                'type_vehicule' => 'Berline',
                'num_plaque' => 'JU 98765',
                'est_defaut' => true,
                'est_actif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
