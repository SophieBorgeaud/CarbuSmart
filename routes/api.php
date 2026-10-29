<?php

use App\Http\Controllers\TypeCarburantController;
use App\Http\Controllers\VehiculeController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Retourne la liste de tous les types de carburants.
Route::get('/types-carburants', [TypeCarburantController::class, 'index']);

// Retourne un type de carburant selon son identifiant.
Route::get('/types-carburants/{id}', [TypeCarburantController::class, 'show']);

// Crée un nouveau compte utilisateur.
Route::post('/register', [AuthController::class, 'register']);

// Authentifie l’utilisateur et retourne un token Sanctum.
Route::post('/login', [AuthController::class, 'login']);

// Routes accessibles uniquement avec un token Sanctum valide.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Retourne la liste de tous les véhicules avec leur type de carburant.
    Route::get('/vehicules', [VehiculeController::class, 'index']);

    // Retourne un véhicule selon son identifiant avec son type de carburant.
    Route::get('/vehicules/{id}', [VehiculeController::class, 'show']);
});
