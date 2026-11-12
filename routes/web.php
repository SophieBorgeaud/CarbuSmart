<?php

use App\Services\StrapiStationService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;

Route::get('/', function () {
    return view('welcome');
});

// Afficher la page avec la carte des stations.
Route::get('/map', function (StrapiStationService $service) {
    // Récupération des stations depuis Strapi
    $stations = $service->getStations();

    // On passe la variable $stations à la vue Blade
    return view('map', ['stations' => $stations]);
})
// Donne le nom 'map' à cette route.
    ->name('map');


// Afficher la page de connexion.
Route::get('/login', function () {
    return view('auth.login');
})
// Donne le nom 'login' à cette route.
    ->name('login');

// Traite les données du formulaire de connexion.
Route::post('/login', [WebAuthController::class, 'login'])
    // Donne le nom 'login.submit' à cette route.
    ->name('login.submit');

// Afficher la page d'inscription.
Route::get('/register', function () {
    return view('auth.register');
})
    // Donne le nom 'register' à cette route.
    ->name('register');

// Traiter les données du formulaire d'inscription.
// Renvoie vers le controller WebAuthController pour exécuter la fonction register.
Route::post('/register', [WebAuthController::class, 'register'])
    // Donne le nom 'register.submit' à cette route.
    ->name('register.submit');
