<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// Champs pouvant être remplis automatiquement à partir d’un tableau.
#[Fillable([
    'type_carburant_id',
    'nom_vehicule',
    'conso_moyenne',
    'marque',
    'modele',
    'type_vehicule',
    'num_plaque',
    'est_defaut',
])]
class Vehicule extends Model
{

    // Relation véhicule 0...* - 1 utilisateur. Le véhicule appartient à un utilisateur.
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    // Relation véhicule 0...* - 1 carburant. Le véhicule appartient à un type de carburant.
    public function typeCarburant(): BelongsTo
    {
        return $this->belongsTo(TypeCarburant::class, 'type_carburant_id');
    }

    // Cette méthode explique à Laravel comment convertir certaines valeurs venant de la base de données.
    // Pour les champs est_defaut et est_actif, Laravel doit toujours les manipuler comme des booléens
    protected function casts(): array
    {
        return [
            'est_defaut' => 'boolean',
            'est_actif' => 'boolean',
        ];
    }

}
