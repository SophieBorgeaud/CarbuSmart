<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// Champs pouvant être remplis automatiquement à partir d’un tableau.
#[Fillable(['nom', 'code'])]
class TypeCarburant extends Model
{
    protected $table = 'types_carburants';  //adaptation de la table

    // Relation véhicule 0...* - 1 type carburant. Un type de carburant peut être utilisé par zéro à plusieurs véhicules.
    public function vehicules(): HasMany
    {
        return $this->hasMany(Vehicule::class, 'type_carburant_id');
    }
}
