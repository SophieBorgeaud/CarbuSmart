<?php

use App\Services\StrapiStationService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/map', function (StrapiStationService $service) {
    // Récupération des stations depuis Strapi
    $stations = $service->getStations();

    // On passe la variable $stations à la vue Blade
    return view('map', ['stations' => $stations]);
});
