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

// Retourne les véhicules actifs de l'utilisateur connecté.
    Route::get('/vehicules', [VehiculeController::class, 'index']);

// Crée un véhicule pour l'utilisateur connecté.
    Route::post('/vehicules', [VehiculeController::class, 'store']);

// Retourne un véhicule actif de l'utilisateur connecté.
    Route::get('/vehicules/{id}', [VehiculeController::class, 'show']);

// Modifie un véhicule de l'utilisateur connecté.
    Route::put('/vehicules/{id}', [VehiculeController::class, 'update']);
    Route::patch('/vehicules/{id}', [VehiculeController::class, 'update']);

// Supprime logiquement un véhicule de l'utilisateur connecté.
    Route::delete('/vehicules/{id}', [VehiculeController::class, 'destroy']);
});
