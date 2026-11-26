@extends('layouts.app')

@section('styles')

    <!-- CSS de Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        /* Adapte la zone principale du layout pour occuper toute la hauteur entre header et footer */
        .main-content {
            padding: 1.25rem;
            display: flex;
            align-items: stretch;
        }

        #map {
            width: 100%;
            flex: 1;
            min-height: 500px;
            border: 3px solid var(--blanc);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(28, 51, 52, 0.15);
            overflow: hidden;
        }

        .popup-content h3 {
            margin: 0 0 5px;
            color: var(--vert-fonce);
        }

        .popup-content p {
            margin: 2px 0;
            font-size: 13px;
        }

        .popup-content ul {
            margin: 5px 0;
            padding-left: 20px;
            font-size: 13px;
        }

        .popup-date {
            margin-top: 8px !important;
            color: #666;
            font-size: 11px;
        }
    </style>

@endsection

@section('content')

    <div class="map-layout">
        @auth
            <aside class="map-sidebar">
                <h2 class="map-sidebar-title">Recommandation</h2>

                <div class="form-group">
                    <label for="vehicule-select">
                        Mon véhicule
                    </label>

                    <select id="vehicule-select" class="form-input">
                        <option value="">Chargement de vos véhicules...</option>
                    </select>

                    <button
                        type="button"
                        id="set-default-vehicule-btn"
                        class="secondary-button map-action-button"
                        style="display: none;"
                    >
                        Définir comme véhicule par défaut
                    </button>
                </div>

                <div class="form-group">
                    <label for="quantite-litres">
                        Quantité de carburant (L)
                    </label>

                    <input
                        class="form-input"
                        type="number"
                        id="quantite-litres"
                        name="quantite_litres"
                        min="1"
                        max="200"
                        step="0.5"
                        value="40"
                    >
                </div>

                <div class="map-actions">
                    <button
                        type="button"
                        id="locate-user-btn"
                        class="secondary-button map-action-button"
                    >
                        📍 Utiliser ma position
                    </button>

                    <button
                        type="button"
                        id="calculate-recommendation-btn"
                        class="primary-button map-action-button"
                    >
                        Calculer la recommandation
                    </button>
                </div>

                <p id="map-status-message" class="map-status-message">
                    Cliquez sur la carte ou utilisez votre position pour afficher les stations recommandées.
                </p>

                <div id="recommendation-list" class="recommendation-list"></div>
            </aside>
        @endauth

        <div class="map-container">
            <div class="map-filter-bar">
                <label for="fuel-filter-select">Filtrer par carburant :</label>
                <select id="fuel-filter-select" class="form-input map-filter-select">
                    <option value="">Tous les carburants</option>
                </select>
                <span id="fuel-filter-count" class="map-filter-count"></span>
            </div>

            <div id="map"></div>
        </div>
    </div>

@endsection

@section('scripts')

    <!-- JavaScript de Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Initialisation de la carte
        const map = L.map('map').setView([47.366, 7.349], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        setTimeout(() => map.invalidateSize(), 100);

        // Variables pour stocker les marqueurs et la position
        let userMarker = null;
        let userLat = null;
        let userLng = null;
        const stationsLayer = L.layerGroup().addTo(map);
        const stationMarkers = [];

        const vehiculeSelect = document.getElementById('vehicule-select');
        const setDefaultVehiculeBtn = document.getElementById('set-default-vehicule-btn');
        const quantiteLitresInput = document.getElementById('quantite-litres');
        const locateUserBtn = document.getElementById('locate-user-btn');
        const calculateBtn = document.getElementById('calculate-recommendation-btn');
        const statusMessage = document.getElementById('map-status-message');
        const recommendationList = document.getElementById('recommendation-list');
        const fuelFilterSelect = document.getElementById('fuel-filter-select');
        const fuelFilterCount = document.getElementById('fuel-filter-count');

        // Stocke les stations initiales pour le filtre global
        const initialStations = {{ Illuminate\Support\Js::from($stations ?? []) }};
        let currentStations = initialStations;

        // Icône rouge pour l'utilisateur
        const userIcon = L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Échappement HTML simple pour éviter les injections XSS dans les popups
        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        @auth
        // Appel à l'endpoint GET /api/vehicules pour charger les véhicules de l'utilisateur connecté
        async function fetchVehiculesFromApi() {
            if (!vehiculeSelect) {
                return;
            }

            try {
                const response = await fetch('/api/vehicules');
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }

                const vehicules = await response.json();
                vehiculeSelect.innerHTML = '';

                if (!Array.isArray(vehicules) || vehicules.length === 0) {
                    vehiculeSelect.innerHTML = '<option value="">Aucun véhicule enregistré</option>';
                    if (setDefaultVehiculeBtn) {
                        setDefaultVehiculeBtn.style.display = 'none';
                    }
                    return;
                }

                vehicules.forEach(vehicule => {
                    const option = document.createElement('option');
                    const carburantNom = vehicule.type_carburant ? vehicule.type_carburant.nom : 'Non renseigné';

                    option.value = vehicule.id;
                    option.dataset.estDefaut = vehicule.est_defaut ? '1' : '0';
                    option.selected = Boolean(vehicule.est_defaut);
                    option.textContent = `${vehicule.nom_vehicule} (${vehicule.marque} ${vehicule.modele} - ${carburantNom})${vehicule.est_defaut ? ' - Véhicule par défaut' : ''}`;

                    vehiculeSelect.appendChild(option);
                });

                updateDefaultButtonVisibility();
            } catch (error) {
                console.error("Erreur lors de la récupération des véhicules :", error);
            }
        }

        // Affiche le bouton "Définir comme véhicule par défaut" uniquement si le véhicule choisi ne l'est pas déjà
        function updateDefaultButtonVisibility() {
            if (!vehiculeSelect || !setDefaultVehiculeBtn) {
                return;
            }

            const selectedOption = vehiculeSelect.options[vehiculeSelect.selectedIndex];
            const isDefault = selectedOption && selectedOption.dataset.estDefaut === '1';

            setDefaultVehiculeBtn.style.display = (selectedOption && selectedOption.value && !isDefault)
                ? 'block'
                : 'none';
        }

        // Appel à l'endpoint PATCH /api/vehicules/{id} pour définir le véhicule par défaut
        async function setDefaultVehiculeViaApi() {
            if (!vehiculeSelect || !vehiculeSelect.value) {
                return;
            }

            try {
                const response = await fetch(`/api/vehicules/${encodeURIComponent(vehiculeSelect.value)}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ est_defaut: true })
                });

                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }

                await fetchVehiculesFromApi();
            } catch (error) {
                console.error("Erreur lors de la mise à jour du véhicule par défaut :", error);
            }
        }

        // Événement : Placement manuel via un clic sur la carte
        map.on('click', function (e) {
            updateUserPositionAndFetchStations(e.latlng.lat, e.latlng.lng);
        });

        // Événement : Récupération de l'emplacement de l'utilisateur
        if (locateUserBtn) {
            locateUserBtn.addEventListener('click', function () {
                if (statusMessage) {
                    statusMessage.textContent = 'Récupération de votre position...';
                }

                if (!navigator.geolocation || !window.isSecureContext) {
                    const center = map.getCenter();
                    updateUserPositionAndFetchStations(center.lat, center.lng);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        updateUserPositionAndFetchStations(
                            position.coords.latitude,
                            position.coords.longitude
                        );
                    },
                    function () {
                        const center = map.getCenter();
                        updateUserPositionAndFetchStations(center.lat, center.lng);
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 5000,
                        maximumAge: 60000
                    }
                );
            });
        }

        // Événements : Changement de véhicule, favori, quantité ou clic sur le bouton de calcul
        if (vehiculeSelect) {
            vehiculeSelect.addEventListener('change', function () {
                updateDefaultButtonVisibility();
                if (userLat !== null && userLng !== null) {
                    fetchStationsFromApi(userLat, userLng);
                }
            });
        }

        if (setDefaultVehiculeBtn) {
            setDefaultVehiculeBtn.addEventListener('click', setDefaultVehiculeViaApi);
        }

        if (quantiteLitresInput) {
            quantiteLitresInput.addEventListener('change', function () {
                if (userLat !== null && userLng !== null) {
                    fetchStationsFromApi(userLat, userLng);
                }
            });
        }

        if (calculateBtn) {
            calculateBtn.addEventListener('click', function () {
                if (userLat !== null && userLng !== null) {
                    fetchStationsFromApi(userLat, userLng);
                } else if (locateUserBtn) {
                    locateUserBtn.click();
                }
            });
        }

        // Chargement initial des véhicules depuis l'API
        fetchVehiculesFromApi();
        @endauth

        // Fonction principale : Met à jour la position et interroge l'API
        function updateUserPositionAndFetchStations(lat, lng) {
            userLat = lat;
            userLng = lng;

            // Placer ou déplacer le marqueur de l'utilisateur
            if (userMarker) {
                userMarker.setLatLng([lat, lng]);
            } else {
                userMarker = L.marker([lat, lng], {icon: userIcon}).addTo(map)
                    .bindPopup("Votre point de départ")
                    .openPopup();
            }

            // Centrer la carte sur l'utilisateur
            map.flyTo([lat, lng], 12);

            // Appeler l'API Laravel
            fetchStationsFromApi(lat, lng);
        }

        // Appel à l'API et affichage des stations
        async function fetchStationsFromApi(lat = null, lng = null) {
            if (lat === null || lng === null) {
                return;
            }

            if (!vehiculeSelect || !vehiculeSelect.value) {
                if (statusMessage) {
                    statusMessage.textContent = 'Veuillez d’abord ajouter ou sélectionner un véhicule dans votre compte pour calculer la recommandation.';
                }
                return;
            }

            let url = `/api/stations/recommandation?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}&vehicule_id=${encodeURIComponent(vehiculeSelect.value)}`;

            if (quantiteLitresInput && quantiteLitresInput.value) {
                url += `&quantite_litres=${encodeURIComponent(quantiteLitresInput.value)}`;
            }

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();

                if (!response.ok) {
                    if (statusMessage) {
                        statusMessage.textContent = result.message || `Erreur HTTP ${response.status}`;
                    }
                    return;
                }

                if (result.status === 'success') {
                    displayStationsOnMap(result.data);
                    // Lorsqu'une recommandation est calculée, on réinitialise le filtre carburant pour afficher le Top 3
                    if (fuelFilterSelect) {
                        fuelFilterSelect.value = '';
                    }
                    currentStations = result.data;
                    displayStationsOnMap(currentStations);
                    displayRecommendedStations(result);
                }
            } catch (error) {
                console.error("Erreur lors de la récupération des stations :", error);
            }
        }

        // Affichage des données renvoyées par l'API /api/stations/recommandation
        function displayRecommendedStations(result) {
            if (!recommendationList) {
                return;
            }

            recommendationList.innerHTML = '';
            const stations = result.data;

            if (!Array.isArray(stations) || stations.length === 0) {
                if (statusMessage) {
                    statusMessage.textContent = result.message || 'Aucune station trouvée à proximité.';
                }
                return;
            }

            if (statusMessage) {
                statusMessage.textContent = result.best_station
                    ? `Meilleure station : ${result.best_station.name} (${result.best_station.estimated_cost} CHF)`
                    : 'Stations les plus proches :';
            }

            stations.forEach((station, index) => {
                const addressParts = [station.address, station.city].filter(Boolean).map(escapeHtml).join(', ');

                let badgeHtml = '';
                if (station.is_best) {
                    badgeHtml = '<span class="vehicle-badge">Recommandée</span>';
                }

                let detailsHtml = '';
                if (station.distance_km !== null && station.distance_km !== undefined) {
                    detailsHtml += `<p>Distance : <strong>${escapeHtml(station.distance_km)} km</strong> (${escapeHtml(station.distance_aller_retour_km)} km A/R)</p>`;
                }

                if (station.fuel_price !== null && station.fuel_price !== undefined) {
                    detailsHtml += `<p>Prix du carburant : <strong>${escapeHtml(station.fuel_price)} CHF/L</strong></p>`;
                }

                if (station.fuel_cost !== null && station.fuel_cost !== undefined) {
                    const litresText = result.quantite_litres ? ` (${escapeHtml(result.quantite_litres)} L)` : '';
                    detailsHtml += `<p>Coût du plein${litresText} : ${escapeHtml(station.fuel_cost)} CHF</p>`;
                }

                if (station.trip_cost !== null && station.trip_cost !== undefined) {
                    detailsHtml += `<p>Coût du trajet : ${escapeHtml(station.trip_cost)} CHF</p>`;
                }

                if (station.estimated_cost !== null && station.estimated_cost !== undefined) {
                    detailsHtml += `<p class="recommendation-cost"><strong>Coût total estimé : ${escapeHtml(station.estimated_cost)} CHF</strong></p>`;
                }

                const card = document.createElement('article');
                card.className = `vehicle-card recommendation-card ${station.is_best ? 'vehicle-card-default' : ''}`;
                card.innerHTML = `
                    <div class="vehicle-card-header">
                        <h4>${escapeHtml(station.name ?? 'Station Service')}</h4>
                        ${badgeHtml}
                    </div>
                    <p><strong>${escapeHtml(station.brand || 'Non spécifiée')}</strong></p>
                    ${addressParts ? `<p>${addressParts}</p>` : ''}
                    ${detailsHtml}
                `;

                card.addEventListener('click', function () {
                    if (station.latitude && station.longitude) {
                        map.flyTo([station.latitude, station.longitude], 14);
                        if (stationMarkers[index]) {
                            stationMarkers[index].openPopup();
                        }
                    }
                });

                recommendationList.appendChild(card);
            });
        }

        // Affichage des marqueurs des stations sur la carte
        function displayStationsOnMap(stations) {
            // Supprimer les anciens marqueurs pour éviter les doublons
            stationsLayer.clearLayers();
            stationMarkers.length = 0;

            if (!Array.isArray(stations)) {
                return;
            }

            stations.forEach((station, index) => {
                if (!station.latitude || !station.longitude) {
                    return;
                }

                // Formatage des carburants
                let fuelsHtml = '<p><em>Prix non disponibles</em></p>';
                if (Array.isArray(station.fuels) && station.fuels.length > 0) {
                    fuelsHtml = '<ul>' + station.fuels.map(fuel =>
                        `<li><strong>${escapeHtml(fuel.fuelCode)}</strong> : ${escapeHtml(fuel.price)} CHF</li>`
                    ).join('') + '</ul>';
                }

                // Formatage de la date de mise à jour
                let dateHtml = '';
                if (station.priceUpdatedAt) {
                    const updateDate = new Date(station.priceUpdatedAt).toLocaleString('fr-CH');
                    dateHtml = `<p class="popup-date">Mis à jour le : ${escapeHtml(updateDate)}</p>`;
                }

                // Formatage de la distance et du coût estimé
                let distanceHtml = '';
                if (station.distance_km !== null && station.distance_km !== undefined) {
                    distanceHtml += `<hr style="margin: 5px 0;"><p>🚗 Distance : <strong>${escapeHtml(station.distance_km)} km</strong></p>`;
                }
                if (station.estimated_cost !== null && station.estimated_cost !== undefined) {
                    distanceHtml += `<p>💰 Coût estimé : <strong>${escapeHtml(station.estimated_cost)} CHF</strong></p>`;
                }

                // Construction de la popup
                const addressParts = [station.address, station.city].filter(Boolean).map(escapeHtml).join(', ');
                const popupContent = `
                <div class="popup-content">
                    <h3>${escapeHtml(station.name ?? 'Station Service')}</h3>
                    <p><strong>Marque :</strong> ${escapeHtml(station.brand || 'Non spécifiée')}</p>
                    ${addressParts ? `<p><strong>Adresse :</strong> ${addressParts}</p>` : ''}
                    ${fuelsHtml}
                    ${dateHtml}
                    ${distanceHtml}
                </div>
            `;

                const marker = L.marker([station.latitude, station.longitude])
                    .bindPopup(popupContent)
                    .addTo(stationsLayer);

                stationMarkers[index] = marker;

                if (station.is_best) {
                    marker.openPopup();
                }
            });

            if (fuelFilterCount) {
                fuelFilterCount.textContent = `${stationMarkers.filter(Boolean).length} station(s) affichée(s)`;
            }
        }

        // Remplit la liste déroulante des types de carburants depuis l'API /api/types-carburants
        async function fetchTypesCarburantsForFilter() {
            if (!fuelFilterSelect) {
                return;
            }

            try {
                const response = await fetch('/api/types-carburants', {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    return;
                }

                const types = await response.json();
                if (!Array.isArray(types)) {
                    return;
                }

                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.code;
                    option.textContent = `${type.nom} (${type.code})`;
                    fuelFilterSelect.appendChild(option);
                });
            } catch (error) {
                console.error("Erreur lors du chargement des types de carburants :", error);
            }
        }

        // Filtre la carte globale selon le carburant sélectionné
        if (fuelFilterSelect) {
            fuelFilterSelect.addEventListener('change', function () {
                const selectedCode = fuelFilterSelect.value.trim().toLowerCase();

                if (!selectedCode) {
                    displayStationsOnMap(initialStations);
                    return;
                }

                const filteredStations = initialStations.filter(station => {
                    const fuels = station.fuels || station.carburants || [];
                    return Array.isArray(fuels) && fuels.some(fuel => {
                        const code = (fuel.fuelCode || fuel.code || '').toString().toLowerCase();
                        return code === selectedCode;
                    });
                });

                displayStationsOnMap(filteredStations);
            });
        }

        // Initialisation au chargement de la page :
        fetchTypesCarburantsForFilter();
        displayStationsOnMap(initialStations);
    </script>

@endsection
