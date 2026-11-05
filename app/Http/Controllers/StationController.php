<?php

namespace App\Http\Controllers;

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
    public function __construct(
        protected StrapiStationService $stationService
    ) {
    }

    public function getWithDistances(Request $request): JsonResponse
    {
        $userLat = $request->input('lat');
        $userLng = $request->input('lng');

        $stations = $this->stationService->getStations();

        if (!is_numeric($userLat) || !is_numeric($userLng)) {
            return response()->json([
                'status' => 'info',
                'message' => 'Position inconnue. Fournissez des coordonnées pour calculer la distance.',
                'data' => $stations,
            ]);
        }

        $stationsWithDistance = $this->calculateDistances(
            $stations,
            (float) $userLat,
            (float) $userLng
        );

        return response()->json([
            'status' => 'success',
            'data' => $stationsWithDistance,
        ]);
    }

    /**
     * Calcule en parallèle les distances routières via OSRM pour chaque station.
     *
     * @param  array<int, array<string, mixed>|object>  $stations
     * @return array<int, array<string, mixed>|object>
     */
    private function calculateDistances(array $stations, float $userLat, float $userLng): array
    {
        if ($stations === []) {
            return [];
        }

        // Exécution des requêtes OSRM en parallèle pour éviter le blocage séquentiel (N+1 HTTP)
        $responses = Http::pool(function (Pool $pool) use ($stations, $userLat, $userLng): array {
            $requests = [];

            foreach ($stations as $index => $station) {
                $lat = is_array($station) ? ($station['latitude'] ?? null) : ($station->latitude ?? null);
                $lng = is_array($station) ? ($station['longitude'] ?? null) : ($station->longitude ?? null);

                if (!is_numeric($lat) || !is_numeric($lng)) {
                    continue;
                }

                $url = sprintf(
                    'https://router.project-osrm.org/route/v1/driving/%F,%F;%F,%F?overview=false',
                    $userLng,
                    $userLat,
                    (float) $lng,
                    (float) $lat
                );

                $requests[(string) $index] = $pool
                    ->as((string) $index)
                    ->connectTimeout(2)
                    ->timeout(3)
                    ->get($url);
            }

            return $requests;
        });

        foreach ($stations as $index => $station) {
            $distanceKm = null;
            $response = $responses[(string) $index] ?? null;

            if ($response instanceof Response && $response->successful() && $response->json('code') === 'Ok') {
                $distanceMeters = $response->json('routes.0.distance');
                if (is_numeric($distanceMeters)) {
                    $distanceKm = round((float) $distanceMeters / 1000, 1);
                }
            } elseif ($response instanceof Throwable) {
                Log::error("Erreur de calcul d'itinéraire OSRM: " . $response->getMessage());
            }

            if (is_array($station)) {
                $station['distance_km'] = $distanceKm;
                $station['estimated_cost'] = null;
            } else {
                $station->distance_km = $distanceKm;
                $station->estimated_cost = null;
            }

            $stations[$index] = $station;
        }

        return $stations;
    }
}
