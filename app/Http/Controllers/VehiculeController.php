<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupère tous les véhicules avec leur type de carburant.
        $datas = Vehicule::with('typeCarburant')->get();

        // Retourne les données au format JSON.
        return $datas;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Recherche le véhicule selon son identifiant avec son type de carburant.
        $data = Vehicule::with('typeCarburant')->findOrFail($id);

        // Retourne le véhicule au format JSON.
        return $data;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
