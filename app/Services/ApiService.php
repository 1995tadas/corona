<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    private const API_URL = "https://api.covid19api.com/";

    protected function performRequest(string $method, string $url, string $query = '', array $parameters = []): string
    {
        $finalUrl = $url . $query;
        if ($method === 'GET') {
            $response = Http::get($finalUrl, $parameters);
        } else {
            abort(405);
        }

        if ($response->successful()) {
            return $response->body();
        } else {
            abort($response->status());
        }
    }

    public function performGetRequest(string $query = '', array $parameters = []): array
    {
        $content = $this->performRequest('GET', self::API_URL, $query, $parameters);
        return json_decode($content);
    }
}
