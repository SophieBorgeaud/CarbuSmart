<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        // Vérifie les données reçues dans la requête.
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Crée le nouvel utilisateur.
        $user = User::create($data);

        // Retourne l’utilisateur créé avec le code HTTP 201.
        return response()->json([
            'message' => 'Utilisateur créé avec succès.',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        // Vérifie les identifiants reçus dans la requête.
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Vérifie que l’adresse e-mail et le mot de passe correspondent.
        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Identifiants incorrects.',
            ], 401);
        }

        // Récupère l’utilisateur authentifié.
        $user = Auth::user();

        // Génère un nouveau token Sanctum.
        $token = $user->createToken('carbusmart-token')->plainTextToken;

        // Retourne le token et les informations de l’utilisateur.
        return response()->json([
            'message' => 'Connexion réussie.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        // Retourne l’utilisateur identifié grâce au token Sanctum.
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        // Supprime le token utilisé pour cette requête.
        $request->user()->currentAccessToken()->delete();

        // Confirme la déconnexion.
        return response()->json([
            'message' => 'Déconnexion réussie.',
        ], 200);
    }
}
