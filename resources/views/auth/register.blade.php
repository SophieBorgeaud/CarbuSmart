@extends('layouts.app')

@section('content')

    <section class="auth-card">

        <h2 class="auth-title">Créer un compte</h2>

        <p class="auth-intro">
            Créez votre compte pour enregistrer vos véhicules et rechercher la station la plus avantageuse.
        </p>

    {{-- Envoie les données du formulaire à la route d'inscription avec la méthode POST. --}}
    <form
        class="auth-form"
        action="{{ route('register.submit') }}"
        method="POST"
    >
        {{-- Protège le formulaire contre les envois provenant d'un autre site. --}}
        {{-- La balise csrf génère un jeton de sécurité lors de l'envoi du formulaire. --}}
        @csrf

        <div class="form-group">
            <label for="nom">Nom</label>
            <input
                class="form-input"
                type="text"
                id="nom"
                name="nom"
                value="{{ old('nom') }}"
                autocomplete="name"
                required
            >

            {{-- Afficher les erreurs de validation. --}}
            @error('nom')
            <p class="form-error">{{ $message }}</p>
            @enderror

        </div>

        <div class="form-group">
            <label for="email">Adresse e-mail</label>
            <input
                class="form-input"
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="email"
                required
            >

            @error('email')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input
                class="form-input"
                type="password"
                id="password"
                name="password"
                autocomplete="new-password"
                required
            >

            @error('password')
            <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                Confirmer le mot de passe
            </label>

            <input
                class="form-input"
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                autocomplete="new-password"
                required
            >
        </div>

        <button class="primary-button" type="submit">
            Créer mon compte
        </button>

    </form>

    <p class="auth-link">
        Vous avez déjà un compte ?
        <br>
        <a href="{{ route('login') }}">Se connecter</a>
    </p>

</section>

@endsection
