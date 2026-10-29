<?php

namespace App\Http\Controllers;

use App\Models\TypeCarburant;
use Illuminate\Http\Request;

class TypeCarburantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupère tous les types de carburants.
        $datas = TypeCarburant::all();

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
        // Recherche le type de carburant selon son identifiant.
        $data = TypeCarburant::findOrFail($id);

        // Retourne le type de carburant au format JSON.
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
