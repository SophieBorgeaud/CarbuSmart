<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use App\Services\StrapiStationService;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class StationController extends Controller
{
    private const DEFAULT_FUEL_LITERS = 40.0;
    private const MAX_RADIUS_KM = 30.0;

    public function __construct(
        protected StrapiStationService $stationService
    ) {
    }

    /**
     * Point d'entrée de l'API : renvoie les 3 meilleures stations (coût plein + trajet).
     */
    public function getBestStations(Request $request): JsonResponse
    {
        $userLat = $request->input('lat');
        $userLng = $request->input('lng');
        $vehiculeId = $request->input('vehicule_id');

        // Validation de la quantité de carburant (40L par défaut)
        $quantiteInput = $request->input('quantite_litres', self::DEFAULT_FUEL_LITERS);
        $fuelLiters = (is_numeric($quantiteInput) && (float) $quantiteInput > 0)
            ? (float) $quantiteInput
            : self::DEFAULT_FUEL_LITERS;

        // Récupération et vérification des droits sur le véhicule
        $vehicule = null;
        if (! empty($vehiculeId)) {
            $vehicule = Vehicule::with('typeCarburant')->find($vehiculeId);

            if (! $vehicule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Véhicule introuvable.',
                ], 404);
            }

            if ($request->user() !== null && (int) $vehicule->utilisateur_id !== (int) $request->user()->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ce véhicule ne vous appartient pas.',
                ], 403);
            }
        }

        // Récupération des stations depuis Strapi
        $stations = $this->stationService->getStations();

        // Si pas de GPS fourni, on renvoie toutes les stations sans calcul
        if (! is_numeric($userLat) || ! is_numeric($userLng)) {
            return response()->json([
                'status' => 'info',
                'message' => 'Position inconnue. Fournissez des coordonnées pour calculer la distance.',
                'data' => $stations,
            ]);
        }

        // Recherche du Top 3 des stations (distance + coût)
        $topStations = $this->findTopStations(
            $stations,
            (float) $userLat,
            (float) $userLng,
            $vehicule,
            $fuelLiters
        );

        // Identification de la meilleure station (la première qui possède un coût estimé)
        $bestStation = null;
        foreach ($topStations as $index => $station) {
            $isBest = ($bestStation === null && $station['estimated_cost'] !== null);
            $topStations[$index]['is_best'] = $isBest;

            if ($isBest) {
                $bestStation = $topStations[$index];
            }
        }

        // Retour de la réponse JSON
        return response()->json([
            'status' => 'success',
            'vehicule' => $vehicule ? [
                'id' => $vehicule->id,
                'nom_vehicule' => $vehicule->nom_vehicule,
                'conso_moyenne' => (float) $vehicule->conso_moyenne,
                'carburant' => $vehicule->typeCarburant?->code,
            ] : null,
            'quantite_litres' => $vehicule ? $fuelLiters : null,
            'best_station' => $bestStation,
            'data' => $topStations,
        ]);
    }

    /**
     * Filtre les stations proches, calcule les coûts (plein + trajet) et renvoie le Top 3.
     */
    private function findTopStations(
        array $stations,
        float $userLat,
        float $userLng,
        ?Vehicule $vehicule,
        float $fuelLiters
    ): array {
        // Garder uniquement les stations dans un carré de 30 km
        $nearbyStations = $this->filterNearbyStations($stations, $userLat, $userLng, self::MAX_RADIUS_KM);

        if (empty($nearbyStations)) {
            return [];
        }

        // Interroger l'API OSRM en parallèle pour avoir les vraies distances routières
        $osrmResponses = Http::pool(function (Pool $pool) use ($nearbyStations, $userLat, $userLng) {
            $requests = [];

            foreach ($nearbyStations as $index => $station) {
                $lat = $station['latitude'];
                $lng = $station['longitude'];
                $url = "https://router.project-osrm.org/route/v1/driving/{$userLng},{$userLat};{$lng},{$lat}?overview=false";

                $requests[$index] = $pool->as((string) $index)->connectTimeout(2)->timeout(3)->get($url);
            }

            return $requests;
        });

        // Infos du véhicule pour le calcul de consommation
        $fuelCode = $vehicule ? ($vehicule->typeCarburant->code ?? null) : null;
        $averageConsumption = $vehicule ? (float) $vehicule->conso_moyenne : null;

        // Calculer la distance et le prix pour chaque station
        foreach ($nearbyStations as $index => $station) {
            $response = $osrmResponses[$index] ?? null;
            $distanceKm = $this->getRouteDistanceKm($response);
            $roundTripKm = $distanceKm !== null ? round($distanceKm * 2, 1) : null;

            $fuelPrice = null;
            $fuelCost = null;
            $tripCost = null;
            $estimatedCost = null;

            if ($fuelCode !== null && $averageConsumption !== null && $distanceKm !== null) {
                $fuelPrice = $this->findFuelPrice($station, $fuelCode);

                if ($fuelPrice !== null) {
                    $fuelCost = round($fuelLiters * $fuelPrice, 2);
                    $tripCost = round((($roundTripKm * $averageConsumption) / 100) * $fuelPrice, 2);
                    $estimatedCost = round($fuelCost + $tripCost, 2);
                }
            }

            $nearbyStations[$index]['distance_km'] = $distanceKm;
            $nearbyStations[$index]['distance_aller_retour_km'] = $roundTripKm;
            $nearbyStations[$index]['fuel_price'] = $fuelPrice;
            $nearbyStations[$index]['fuel_cost'] = $fuelCost;
            $nearbyStations[$index]['trip_cost'] = $tripCost;
            $nearbyStations[$index]['estimated_cost'] = $estimatedCost;
        }

        // Trier par coût total le moins cher, puis par distance la plus proche
        usort($nearbyStations, function (array $a, array $b) {
            $costA = $a['estimated_cost'] ?? 999999;
            $costB = $b['estimated_cost'] ?? 999999;

            if ($costA !== $costB) {
                return $costA <=> $costB;
            }

            $distA = $a['distance_km'] ?? 999999;
            $distB = $b['distance_km'] ?? 999999;

            return $distA <=> $distB;
        });

        // Retourner uniquement le Top 3
        return array_slice($nearbyStations, 0, 3);
    }

    /**
     * Filtre les stations dans un carré (Bounding Box) autour de l'utilisateur
     * et convertit chaque station en tableau associatif.
     */
    private function filterNearbyStations(array $stations, float $userLat, float $userLng, float $radiusKm): array
    {
        // 1 degré de latitude = ~111 km
        $latDelta = $radiusKm / 111.0;
        $lngDelta = $radiusKm / (111.0 * cos(deg2rad($userLat)));

        $minLat = $userLat - $latDelta;
        $maxLat = $userLat + $latDelta;
        $minLng = $userLng - $lngDelta;
        $maxLng = $userLng + $lngDelta;

        $nearbyStations = [];

        foreach ($stations as $station) {
            // Conversion en tableau une seule fois pour éviter les "is_array()" partout
            $stationArray = (array) $station;

            $lat = $stationArray['latitude'] ?? null;
            $lng = $stationArray['longitude'] ?? null;

            if (is_numeric($lat) && is_numeric($lng)) {
                if ($lat >= $minLat && $lat <= $maxLat && $lng >= $minLng && $lng <= $maxLng) {
                    $nearbyStations[] = $stationArray;
                }
            }
        }

        return $nearbyStations;
    }

    /**
     * Lit la réponse HTTP d'OSRM et renvoie la distance routière en kilomètres (ou null si erreur).
     */
    private function getRouteDistanceKm(mixed $response): ?float
    {
        if ($response instanceof Response && $response->successful() && $response->json('code') === 'Ok') {
            $distanceMeters = $response->json('routes.0.distance');

            if (is_numeric($distanceMeters)) {
                return round($distanceMeters / 1000, 1);
            }
        } elseif ($response instanceof Throwable) {
            Log::error("Erreur de calcul d'itinéraire OSRM : " . $response->getMessage());
        }

        return null;
    }

    /**
     * Cherche dans les carburants d'une station le prix au litre correspondant au véhicule.
     */
    private function findFuelPrice(array $station, string $targetFuelCode): ?float
    {
        $fuels = $station['fuels'] ?? $station['carburants'] ?? [];

        foreach ($fuels as $fuel) {
            $fuelArray = (array) $fuel;

            $code = $fuelArray['fuelCode'] ?? $fuelArray['code'] ?? null;

            if (is_string($code) && strtolower($code) === strtolower($targetFuelCode)) {
                $price = $fuelArray['price'] ?? $fuelArray['prix_litre'] ?? null;

                if (is_numeric($price) && (float) $price > 0) {
                    return (float) $price;
                }
            }
        }

        return null;
    }
}
