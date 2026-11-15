<?php

namespace App\Http\Controllers;

use App\Models\TypeCarburant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompteController extends Controller
{
    /**
     * Affiche le compte et les véhicules actifs de l'utilisateur connecté.
     */
    public function index(Request $request)
    {
        $vehicules = $request->user()
            ->vehicules()
            ->with('typeCarburant')
            ->where('est_actif', true)
            ->get();

        $typesCarburants = TypeCarburant::orderBy('nom')->get();

        return view('compte.index', [
            'vehicules' => $vehicules,
            'typesCarburants' => $typesCarburants,
        ]);
    }

    /**
     * Modifie le nom et l'adresse e-mail de l'utilisateur connecté.
     */
    public function updateProfil(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate(
            [
                'nom' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
            ],
            [
                'nom.required' => 'Le nom est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',
                'email.required' => 'L’adresse e-mail est obligatoire.',
                'email.email' => 'L’adresse e-mail n’est pas valide.',
                'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            ]
        );

        $user->update($validated);

        return redirect()
            ->route('compte.index')
            ->with('success', 'Vos informations personnelles ont été modifiées.');
    }

    /**
     * Modifie le mot de passe de l'utilisateur connecté.
     */
    public function updateMotDePasse(Request $request)
    {
        $validated = $request->validate(
            [
                'current_password' => 'required|current_password',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                'current_password.required' => 'Le mot de passe actuel est obligatoire.',
                'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
                'password.required' => 'Le nouveau mot de passe est obligatoire.',
                'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
                'password.confirmed' => 'Les deux nouveaux mots de passe ne correspondent pas.',
            ]
        );

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('compte.index')
            ->with('success', 'Votre mot de passe a été modifié.');
    }

    /**
     * Enregistre un véhicule pour l'utilisateur connecté.
     */
    public function storeVehicule(Request $request)
    {
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

        // Vérifie si l'utilisateur possède déjà un véhicule actif.
        $possedeDejaUnVehicule = $request->user()
            ->vehicules()
            ->where('est_actif', true)
            ->exists();

        // Le premier véhicule devient automatiquement celui par défaut.
        $validated['est_defaut'] = !$possedeDejaUnVehicule
            || $request->boolean('est_defaut');

        // Retire le statut par défaut aux autres véhicules.
        if ($validated['est_defaut']) {
            $request->user()
                ->vehicules()
                ->update(['est_defaut' => false]);
        }

        // Crée le véhicule et le lie à l'utilisateur connecté.
        $request->user()
            ->vehicules()
            ->create($validated);

        return redirect()
            ->route('compte.index')
            ->with('success', 'Votre véhicule a été ajouté.');
    }

    /**
     * Modifie un véhicule appartenant à l'utilisateur connecté.
     */
    public function updateVehicule(Request $request, string $id)
    {
        // Recherche uniquement un véhicule actif de l'utilisateur connecté.
        $vehicule = $request->user()
            ->vehicules()
            ->where('est_actif', true)
            ->findOrFail($id);

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

        // Convertit la case à cocher en valeur vraie ou fausse.
        $validated['est_defaut'] = $request->boolean('est_defaut');

        // Un véhicule déjà défini par défaut conserve ce statut
        // tant qu'un autre véhicule n'est pas choisi comme véhicule par défaut.
        if ($vehicule->est_defaut && !$validated['est_defaut']) {
            $validated['est_defaut'] = true;
        }

        // Si ce véhicule devient celui par défaut,
        // retire ce statut aux autres véhicules.
        if ($validated['est_defaut']) {
            $request->user()
                ->vehicules()
                ->where('id', '!=', $vehicule->id)
                ->update(['est_defaut' => false]);
        }

        $vehicule->update($validated);

        return redirect()
            ->route('compte.index')
            ->with('success', 'Votre véhicule a été modifié.');
    }

    /**
     * Désactive un véhicule appartenant à l'utilisateur connecté.
     */
    public function destroyVehicule(Request $request, string $id)
    {
        // Recherche uniquement un véhicule actif de l'utilisateur connecté.
        $vehicule = $request->user()
            ->vehicules()
            ->where('est_actif', true)
            ->findOrFail($id);

        // Mémorise si le véhicule supprimé était celui par défaut.
        $etaitVehiculeDefaut = $vehicule->est_defaut;

        // Désactive le véhicule au lieu de le supprimer de la base de données.
        $vehicule->est_actif = false;
        $vehicule->est_defaut = false;
        $vehicule->save();

        // Si le véhicule supprimé était celui par défaut,
        // le premier véhicule actif restant devient celui par défaut.
        if ($etaitVehiculeDefaut) {
            $nouveauVehiculeDefaut = $request->user()
                ->vehicules()
                ->where('est_actif', true)
                ->orderBy('id')
                ->first();

            if ($nouveauVehiculeDefaut) {
                $nouveauVehiculeDefaut->est_defaut = true;
                $nouveauVehiculeDefaut->save();
            }
        }

        return redirect()
            ->route('compte.index')
            ->with('success', 'Votre véhicule a été supprimé.');
    }
}
