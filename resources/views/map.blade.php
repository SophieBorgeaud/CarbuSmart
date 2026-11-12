@extends('layouts.app')

@section('styles')

    <!-- CSS de Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        /* Adapte la zone principale du layout à la carte */
        .main-content {
            padding: 0;
            display: block;
        }

        #map {
            width: 100%;
            height: 70vh;
            min-height: 500px;
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

    <div id="map"></div>

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

        // Variables pour stocker les marqueurs
        let userMarker = null;
        const stationsLayer = L.layerGroup().addTo(map);

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

        // Événement : Placement manuel via un clic sur la carte
        map.on('click', function (e) {
            updateUserPositionAndFetchStations(e.latlng.lat, e.latlng.lng);
        });

        // Fonction principale : Met à jour la position et interroge l'API
        function updateUserPositionAndFetchStations(lat, lng) {
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
            let url = '/api/stations/distances';
            if (lat !== null && lng !== null) {
                url += `?lat=${encodeURIComponent(lat)}&lng=${encodeURIComponent(lng)}`;
            }

            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Erreur HTTP ${response.status}`);
                }

                const result = await response.json();

                if (result.status === 'success' || result.status === 'info') {
                    displayStationsOnMap(result.data);
                }
            } catch (error) {
                console.error("Erreur lors de la récupération des stations :", error);
            }
        }

        // Affichage des marqueurs des stations sur la carte
        function displayStationsOnMap(stations) {
            // Supprimer les anciens marqueurs pour éviter les doublons
            stationsLayer.clearLayers();

            if (!Array.isArray(stations)) {
                return;
            }

            stations.forEach(station => {
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

                // Formatage de la distance
                let distanceHtml = '';
                if (station.distance_km !== null && station.distance_km !== undefined) {
                    distanceHtml = `<hr style="margin: 5px 0;"><p>🚗 Distance : <strong>${escapeHtml(station.distance_km)} km</strong></p>`;
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

                L.marker([station.latitude, station.longitude])
                    .bindPopup(popupContent)
                    .addTo(stationsLayer);
            });
        }

        // Initialisation au chargement de la page :
        // Utilise les stations déjà passées par la route /map pour éviter un double appel API
        const initialStations = {{ Illuminate\Support\Js::from($stations ?? []) }};
        if (initialStations.length > 0) {
            displayStationsOnMap(initialStations);
        } else {
            fetchStationsFromApi();
        }
    </script>

@endsection
