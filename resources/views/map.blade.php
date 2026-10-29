<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarbuSmart - Carte des stations</title>

    <!-- CSS de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body { margin: 0; padding: 0; }
        #map { height: 100vh; width: 100%; }

        .popup-content h3 { margin: 0 0 5px 0; color: #2D4840; }
        .popup-content p { margin: 2px 0; font-size: 13px; }
        .popup-content ul { margin: 5px 0; padding-left: 20px; font-size: 13px; }
        .popup-date { font-size: 11px; color: #666; margin-top: 8px !important; }
    </style>
</head>
<body>

<div id="map"></div>

<!-- JS de Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // 1. Initialisation de la carte centrée sur Delémont
    const map = L.map('map').setView([47.3667, 7.3500], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // 2. BEST PRACTICE LARAVEL : Injection sécurisée des données PHP vers JS
    const stations = {{ Illuminate\Support\Js::from($stations) }};

    // 3. Création des marqueurs
    stations.forEach(station => {
        if (station.latitude && station.longitude) {

            // Formatage des carburants
            let fuelsHtml = '';
            if (station.fuels && station.fuels.length > 0) {
                fuelsHtml = '<ul>' + station.fuels.map(fuel =>
                    `<li><strong>${fuel.fuelCode}</strong> : ${fuel.price} CHF</li>`
                ).join('') + '</ul>';
            } else {
                fuelsHtml = '<p><em>Prix non disponibles</em></p>';
            }

            // Formatage de la date de mise à jour
            let dateHtml = '';
            if (station.priceUpdatedAt) {
                const updateDate = new Date(station.priceUpdatedAt).toLocaleString('fr-CH');
                dateHtml = `<p class="popup-date">Mis à jour le : ${updateDate}</p>`;
            }

            // Construction de la popup
            const popupContent = `
                    <div class="popup-content">
                        <h3>${station.name}</h3>
                        <p><strong>Marque :</strong> ${station.brand || 'Non spécifiée'}</p>
                        <p><strong>Adresse :</strong> ${station.address}, ${station.city}</p>
                        ${fuelsHtml}
                        ${dateHtml}
                    </div>
                `;

            // Ajout sur la carte
            L.marker([station.latitude, station.longitude])
                .addTo(map)
                .bindPopup(popupContent);
        }
    });
</script>
</body>
</html>
