<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    /**
     * Crée un compte puis connecte automatiquement l'utilisateur.
     */
    public function register(Request $request)
    {
        // Vérifie les données envoyées par le formulaire.
        // Définit les messages d'erreur à afficher.
        $validated = $request->validate(
            [
                'nom' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                'nom.required' => 'Le nom est obligatoire.',
                'nom.max' => 'Le nom ne peut pas dépasser 255 caractères.',

                'email.required' => 'L’adresse e-mail est obligatoire.',
                'email.email' => 'L’adresse e-mail n’est pas valide.',
                'email.unique' => 'Cette adresse e-mail est déjà utilisée.',

                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            ]
        );

        // Crée l'utilisateur dans la base de données.
        $user = User::create([
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Connecte automatiquement le nouvel utilisateur.
        Auth::login($user);

        // Renouvelle l'identifiant de session pour sécuriser la connexion.
        $request->session()->regenerate();

        // Redirige l'utilisateur vers la carte.
        return redirect()
            ->route('map')
            ->with('success', 'Votre compte a été créé avec succès.');
    }

    /**
     * Authentifie l'utilisateur avec son adresse e-mail et son mot de passe.
     */
    public function login(Request $request)
    {
        // Vérifie les données envoyées par le formulaire.
        $credentials = $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required|string',
            ],
            [
                'email.required' => 'L’adresse e-mail est obligatoire.',
                'email.email' => 'L’adresse e-mail doit être valide.',
                'password.required' => 'Le mot de passe est obligatoire.',
            ]
        );

        // Vérifie si l'adresse e-mail et le mot de passe correspondent.
        if (!Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'L’adresse e-mail ou le mot de passe est incorrect.',
                ])
                ->onlyInput('email');
        }

        // Renouvelle l'identifiant de session après la connexion.
        $request->session()->regenerate();

        // Redirige l'utilisateur connecté vers la carte.
        return redirect()
            ->route('map')
            ->with('success', 'Vous êtes maintenant connecté.');
    }
}
