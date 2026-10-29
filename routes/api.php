<?php

use App\Http\Controllers\TypeCarburantController;
use Illuminate\Support\Facades\Route;

// Retourne la liste de tous les types de carburants.
Route::get('/types-carburants', [TypeCarburantController::class, 'index']);

// Retourne un type de carburant selon son identifiant.
Route::get('/types-carburants/{id}', [TypeCarburantController::class, 'show']);







