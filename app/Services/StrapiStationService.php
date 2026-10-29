<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class StrapiStationService
{
    protected PendingRequest $client;

    public function __construct()
    {
        // Setup the base HTTP client with the Strapi URL and token
        $this->client = Http::baseUrl(config('services.strapi.url'))
            ->withToken(config('services.strapi.token'))
            ->acceptJson();
    }

    /**
     * Retrieve a list of all stations
     *
     * @return array
     * @throws RequestException
     */
    public function getStations(): array
    {
        // Fetch all stations with optional populate parameter for relations
        $response = $this->client->get('/stations', [
            'populate' => '*'
        ]);

        // Automatically throw an exception for 4xx and 5xx errors
        $response->throw();

        // Strapi v4/v5 wraps the payload in a 'data' object
        return $response->json('data', []);
    }

    /**
     * Retrieve a single station by its Strapi Document ID
     *
     * @param string $stationStrapiId
     * @return array|null
     * @throws RequestException
     */
    public function getStation(string $stationStrapiId): ?array
    {
        $response = $this->client->get("/stations/{$stationStrapiId}", [
            'populate' => '*'
        ]);

        $response->throw();

        return $response->json('data');
    }
}
