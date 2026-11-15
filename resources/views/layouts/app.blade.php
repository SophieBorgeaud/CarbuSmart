<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CarbuSmart</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    @yield('styles')

</head>

<body>

<header class="site-header">
    <div class="header-content">

        <h1 class="site-title">CarbuSmart</h1>

        <nav class="main-nav" aria-label="Navigation principale">
            <ul class="nav-list">

                <li>
                    <a class="nav-link" href="{{ route('map') }}">
                        Carte des stations
                    </a>
                </li>

                {{-- Liens affichés uniquement aux visiteurs non connectés. --}}
                @guest
                    <li>
                        <a class="nav-link" href="{{ route('login') }}">
                            Connexion
                        </a>
                    </li>

                    <li>
                        <a class="nav-link" href="{{ route('register') }}">
                            Créer un compte
                        </a>
                    </li>
                @endguest

                {{-- Bouton affiché uniquement à l'utilisateur connecté. --}}
                @auth
                    <li>
                        <a class="nav-link" href="{{ route('compte.index') }}">
                            Mon compte
                        </a>
                    </li>
                    <li>
                        <form
                            class="logout-form"
                            action="{{ route('logout') }}"
                            method="POST"
                        >
                            @csrf

                            <button class="nav-link nav-button" type="submit">
                                Déconnexion
                            </button>
                        </form>
                    </li>
                @endauth

            </ul>
        </nav>

    </div>
</header>

<main class="main-content">
    @yield('content')
</main>

<footer class="site-footer">
    <p>
        &copy; 2026 CarbuSmart – Sophie Borgeaud & Lou Lehmann – ESIG
    </p>
</footer>

@yield('scripts')

</body>
</html>
