@extends('layouts.app')

@section('content')

    <section class="account-section">

        <header class="account-header">
            <h2>Mon compte</h2>

            <p>
                Bienvenue, {{ auth()->user()->nom }}.
            </p>
        </header>

        @if(session('success'))
            <p class="success-message">
                {{ session('success') }}
            </p>
        @endif

        <section class="account-settings">

            <details
                class="account-details"
                @if($errors->has('nom') || $errors->has('email')) open @endif
            >
                <summary class="account-summary">
                    Modifier mes informations personnelles
                </summary>

                <form
                    class="account-form"
                    action="{{ route('compte.profil.update') }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="nom">
                            Nom
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            id="nom"
                            name="nom"
                            value="{{ old('nom', auth()->user()->nom) }}"
                            required
                        >

                        @error('nom')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">
                            Adresse e-mail
                        </label>

                        <input
                            class="form-input"
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', auth()->user()->email) }}"
                            required
                        >

                        @error('email')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <button class="primary-button" type="submit">
                        Enregistrer mes informations
                    </button>

                </form>
            </details>

            <details
                class="account-details"
                @if($errors->has('current_password') || $errors->has('password')) open @endif
            >
                <summary class="account-summary">
                    Modifier mon mot de passe
                </summary>

                <form
                    class="account-form"
                    action="{{ route('compte.mot-de-passe.update') }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="current_password">
                            Mot de passe actuel
                        </label>

                        <input
                            class="form-input"
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            required
                        >

                        @error('current_password')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">
                            Nouveau mot de passe
                        </label>

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
                            Confirmer le nouveau mot de passe
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
                        Modifier mon mot de passe
                    </button>

                </form>
            </details>

        </section>

        <h3>Mes véhicules</h3>

        <div class="vehicle-grid">

            @forelse($vehicules as $vehicule)

                <article
                    class="vehicle-card {{ $vehicule->est_defaut ? 'vehicle-card-default' : '' }}"
                >

                    <div class="vehicle-card-header">
                        <h4>{{ $vehicule->nom_vehicule }}</h4>

                        @if($vehicule->est_defaut)
                            <span class="vehicle-badge">
                                Véhicule par défaut
                            </span>
                        @endif
                    </div>

                    <p>
                        <strong>
                            {{ $vehicule->marque }} {{ $vehicule->modele }}
                        </strong>
                    </p>

                    <p>
                        Type : {{ $vehicule->type_vehicule }}
                    </p>

                    <p>
                        Carburant :
                        {{ $vehicule->typeCarburant?->nom ?? 'Non renseigné' }}
                    </p>

                    <p>
                        Consommation :
                        {{ number_format($vehicule->conso_moyenne, 2, ',', ' ') }}
                        L/100 km
                    </p>

                    <p>
                        Plaque : {{ $vehicule->num_plaque }}
                    </p>

                    <details class="vehicle-edit">

                        <summary class="secondary-button">
                            Modifier
                        </summary>

                        <form
                            class="vehicle-edit-form"
                            action="{{ route('compte.vehicules.update', $vehicule->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="nom_vehicule_{{ $vehicule->id }}">
                                    Nom du véhicule
                                </label>

                                <input
                                    class="form-input"
                                    type="text"
                                    id="nom_vehicule_{{ $vehicule->id }}"
                                    name="nom_vehicule"
                                    value="{{ $vehicule->nom_vehicule }}"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="marque_{{ $vehicule->id }}">
                                    Marque
                                </label>

                                <input
                                    class="form-input"
                                    type="text"
                                    id="marque_{{ $vehicule->id }}"
                                    name="marque"
                                    value="{{ $vehicule->marque }}"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="modele_{{ $vehicule->id }}">
                                    Modèle
                                </label>

                                <input
                                    class="form-input"
                                    type="text"
                                    id="modele_{{ $vehicule->id }}"
                                    name="modele"
                                    value="{{ $vehicule->modele }}"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="type_vehicule_{{ $vehicule->id }}">
                                    Type de véhicule
                                </label>

                                <input
                                    class="form-input"
                                    type="text"
                                    id="type_vehicule_{{ $vehicule->id }}"
                                    name="type_vehicule"
                                    value="{{ $vehicule->type_vehicule }}"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="type_carburant_{{ $vehicule->id }}">
                                    Carburant
                                </label>

                                <select
                                    class="form-input"
                                    id="type_carburant_{{ $vehicule->id }}"
                                    name="type_carburant_id"
                                    required
                                >
                                    @foreach($typesCarburants as $typeCarburant)
                                        <option
                                            value="{{ $typeCarburant->id }}"
                                            @selected($vehicule->type_carburant_id == $typeCarburant->id)
                                        >
                                            {{ $typeCarburant->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="conso_moyenne_{{ $vehicule->id }}">
                                    Consommation (L/100 km)
                                </label>

                                <input
                                    class="form-input"
                                    type="number"
                                    step="0.01"
                                    min="0.1"
                                    max="100"
                                    id="conso_moyenne_{{ $vehicule->id }}"
                                    name="conso_moyenne"
                                    value="{{ $vehicule->conso_moyenne }}"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="num_plaque_{{ $vehicule->id }}">
                                    Plaque
                                </label>

                                <input
                                    class="form-input"
                                    type="text"
                                    id="num_plaque_{{ $vehicule->id }}"
                                    name="num_plaque"
                                    value="{{ $vehicule->num_plaque }}"
                                    required
                                >
                            </div>

                            <label class="checkbox-group">
                                <input
                                    type="checkbox"
                                    name="est_defaut"
                                    value="1"
                                    @checked($vehicule->est_defaut)
                                >

                                Véhicule par défaut
                            </label>

                            <button
                                class="cancel-button"
                                type="button"
                                onclick="
                                    this.form.reset();
                                    this.closest('details').removeAttribute('open');
                                "
                            >
                                Annuler les modifications
                            </button>

                            <button class="primary-button" type="submit">
                                Enregistrer les modifications
                            </button>

                        </form>

                        <form
                            class="vehicle-delete-form"
                            action="{{ route('compte.vehicules.destroy', $vehicule->id) }}"
                            method="POST"
                            onsubmit="return confirm('Voulez-vous vraiment supprimer ce véhicule ?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="danger-button" type="submit">
                                Supprimer le véhicule
                            </button>
                        </form>

                    </details>

                </article>

            @empty

                <p>
                    Vous n’avez encore enregistré aucun véhicule.
                </p>

            @endforelse

        </div>

        <details
            class="vehicle-add"
            @if($errors->any()) open @endif
        >

            <summary class="vehicle-add-summary">
                Ajouter un véhicule
            </summary>

            <section class="vehicle-form-section">

                <h3>Ajouter un véhicule</h3>

                <form
                    class="vehicle-form"
                    action="{{ route('compte.vehicules.store') }}"
                    method="POST"
                >
                    @csrf

                    <div class="form-group">
                        <label for="nom_vehicule">
                            Nom du véhicule
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            id="nom_vehicule"
                            name="nom_vehicule"
                            value="{{ old('nom_vehicule') }}"
                            required
                        >

                        @error('nom_vehicule')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="marque">
                            Marque
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            id="marque"
                            name="marque"
                            value="{{ old('marque') }}"
                            required
                        >

                        @error('marque')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="modele">
                            Modèle
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            id="modele"
                            name="modele"
                            value="{{ old('modele') }}"
                            required
                        >

                        @error('modele')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="type_vehicule">
                            Type de véhicule
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            id="type_vehicule"
                            name="type_vehicule"
                            value="{{ old('type_vehicule') }}"
                            required
                        >

                        @error('type_vehicule')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="type_carburant_id">
                            Carburant
                        </label>

                        <select
                            class="form-input"
                            id="type_carburant_id"
                            name="type_carburant_id"
                            required
                        >
                            <option value="">
                                Choisir un carburant
                            </option>

                            @foreach($typesCarburants as $typeCarburant)
                                <option
                                    value="{{ $typeCarburant->id }}"
                                    @selected(old('type_carburant_id') == $typeCarburant->id)
                                >
                                    {{ $typeCarburant->nom }}
                                </option>
                            @endforeach
                        </select>

                        @error('type_carburant_id')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="conso_moyenne">
                            Consommation moyenne (L/100 km)
                        </label>

                        <input
                            class="form-input"
                            type="number"
                            step="0.01"
                            min="0.1"
                            max="100"
                            id="conso_moyenne"
                            name="conso_moyenne"
                            value="{{ old('conso_moyenne') }}"
                            required
                        >

                        @error('conso_moyenne')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="num_plaque">
                            Plaque d’immatriculation
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            id="num_plaque"
                            name="num_plaque"
                            value="{{ old('num_plaque') }}"
                            required
                        >

                        @error('num_plaque')
                        <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="checkbox-group">
                        <input
                            type="checkbox"
                            name="est_defaut"
                            value="1"
                            @checked(old('est_defaut'))
                        >

                        Définir comme véhicule par défaut
                    </label>

                    <button class="primary-button" type="submit">
                        Ajouter le véhicule
                    </button>

                </form>

            </section>

        </details>

    </section>

@endsection
