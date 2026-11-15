@extends('layouts.app')

@section('content')

    <section class="auth-card">

        <h2 class="auth-title">Connexion</h2>

        <p class="auth-intro">
            Connectez-vous pour accéder à vos véhicules et à vos stations favorites.
        </p>

        {{-- Envoie les identifiants à la route de connexion avec la méthode POST. --}}
        <form
            class="auth-form"
            action="{{ route('login.submit') }}"
            method="POST"
        >
            {{-- Protège le formulaire contre les envois provenant d'un autre site. --}}
            @csrf

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
                    autocomplete="current-password"
                    required
                >

                @error('password')
                <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button class="primary-button" type="submit">
                Se connecter
            </button>

        </form>

        <p class="auth-link">
            Vous n’avez pas encore de compte ?
            <br>
            <a href="{{ route('register') }}">Créer un compte</a>
        </p>

    </section>

@endsection
