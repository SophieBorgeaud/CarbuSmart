<?php

use App\Http\Controllers\TypeCarburantController;
use App\Http\Controllers\VehiculeController;
use Illuminate\Support\Facades\Route;

// Retourne la liste de tous les types de carburants.
Route::get('/types-carburants', [TypeCarburantController::class, 'index']);

// Retourne un type de carburant selon son identifiant.
Route::get('/types-carburants/{id}', [TypeCarburantController::class, 'show']);

// Retourne la liste de tous les véhicules avec leur type de carburant.
Route::get('/vehicules', [VehiculeController::class, 'index']);

// Retourne un véhicule selon son identifiant avec son type de carburant.
Route::get('/vehicules/{id}', [VehiculeController::class, 'show']);



