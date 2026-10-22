<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// Champs pouvant être remplis automatiquement à partir d’un tableau.
#[Fillable(['station_strapi_id'])]
class Favori extends Model
{
    // Relation favori 0...* - 1 utilisateur. Un favori appartient à un utilisateur.
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
