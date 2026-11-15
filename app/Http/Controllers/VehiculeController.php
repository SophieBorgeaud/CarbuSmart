<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use Illuminate\Http\Request;

class VehiculeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Récupère uniquement les véhicules actifs de l'utilisateur connecté.
        $vehicules = $request->user()
            ->vehicules()
            ->with('typeCarburant')
            ->where('est_actif', true)
            ->get();

        // Retourne les véhicules au format JSON.
        return response()->json($vehicules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Valide les données reçues pour créer un véhicule.
        $validated = $request->validate([
            'type_carburant_id' => 'required|integer|exists:types_carburants,id',
            'nom_vehicule' => 'required|string|max:255',
            'conso_moyenne' => 'required|numeric|min:0.1|max:100',
            'marque' => 'required|string|max:255',
            'modele' => 'required|string|max:255',
            'type_vehicule' => 'required|string|max:255',
            'num_plaque' => 'required|string|max:255',
            'est_defaut' => 'sometimes|boolean',
        ]);

        // Si ce véhicule devient celui par défaut, retire ce statut aux autres.
        if ($validated['est_defaut'] ?? false) {
            $request->user()
                ->vehicules()
                ->update(['est_defaut' => false]);
        }

        // Crée le véhicule et le lie automatiquement à l'utilisateur connecté.
        $vehicule = $request->user()
            ->vehicules()
            ->create($validated);

        // Charge le type de carburant dans la réponse.
        $vehicule->load('typeCarburant');

        return response()->json([
            'message' => 'Véhicule créé avec succès.',
            'data' => $vehicule,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Recherche un véhicule actif appartenant à l'utilisateur connecté.
        $vehicule = $request->user()
            ->vehicules()
            ->with('typeCarburant')
            ->where('est_actif', true)
            ->findOrFail($id);

        // Retourne le véhicule au format JSON.
        return $vehicule;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Recherche le véhicule actif appartenant à l'utilisateur connecté.
        $vehicule = $request->user()
            ->vehicules()
            ->where('est_actif', true)
            ->findOrFail($id);

        // Valide les données reçues pour modifier le véhicule.
        $validated = $request->validate([
            'type_carburant_id' => 'sometimes|required|integer|exists:types_carburants,id',
            'nom_vehicule' => 'sometimes|required|string|max:255',
            'conso_moyenne' => 'sometimes|required|numeric|min:0.1|max:100',
            'marque' => 'sometimes|required|string|max:255',
            'modele' => 'sometimes|required|string|max:255',
            'type_vehicule' => 'sometimes|required|string|max:255',
            'num_plaque' => 'sometimes|required|string|max:255',
            'est_defaut' => 'sometimes|boolean',
        ]);

        // Si ce véhicule devient celui par défaut, retire ce statut aux autres.
        if ($validated['est_defaut'] ?? false) {
            $request->user()
                ->vehicules()
                ->where('id', '!=', $vehicule->id)
                ->update(['est_defaut' => false]);
        }

        // Modifie le véhicule avec les données validées.
        $vehicule->update($validated);

        // Recharge le type de carburant après la modification.
        $vehicule->load('typeCarburant');

        // Retourne le véhicule modifié.
        return response()->json([
            'message' => 'Véhicule modifié avec succès.',
            'data' => $vehicule,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Recherche le véhicule actif appartenant à l'utilisateur connecté.
        $vehicule = $request->user()
            ->vehicules()
            ->where('est_actif', true)
            ->findOrFail($id);

        // Désactive le véhicule sans le supprimer de la base de données et retire son statut par défaut.
        // Attribution directe pour modifier est_actif sans l'ajouter à Fillable et sans permettre sa modification depuis le JSON.
        $vehicule->est_actif = false;
        $vehicule->est_defaut = false;
        $vehicule->save();

        // Confirme la suppression du véhicule à l'utilisateur.
        return response()->json([
            'message' => 'Véhicule supprimé avec succès.',
        ]);
    }
}
