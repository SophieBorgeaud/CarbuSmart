<?php



namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Champs pouvant être remplis automatiquement à partir d’un tableau.
#[Fillable(['nom', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Relation utilisateur 1 - 0...* véhicule. Un utilisateur possède zéro à plusieurs véhicules.
    public function vehicules(): HasMany
    {
        return $this->hasMany(Vehicule::class, 'utilisateur_id');
    }

    // Relation utilisateur 1 - 0...* favori. Un utilisateur possède zéro à plusieurs favoris.
    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class, 'utilisateur_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
