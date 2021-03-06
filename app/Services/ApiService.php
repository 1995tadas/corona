<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\http\Client\Response;
use Illuminate\Support\Facades\Http;

class ApiService
{
    private function performRequest(string $method, string $url, string $query = '', array $parameters = []): string
    {
        $finalUrl = $url . $query;
        if ($method === 'GET') {
            $response = Http::withOptions([
                'verify' => false,
                'stream' => true
            ])->withHeaders([
                'accept-encoding' => 'gzip',
            ])->get($finalUrl, $parameters);
        } else {
            abort(405);
        }

        $this->rateLimitReset($response);
        if ($response->successful()) {
            $data = '';
            $body = $response->getBody();
            while (!$body->eof()) {
                $data .= $body->read(1024);
            }

            return $data;
        } else {
            abort($response->status());
        }
    }

    public function performGetRequest(string $apiUrl, string $query = '', array $parameters = [])
    {
        $content = $this->performRequest('GET', $apiUrl, $query, $parameters);
        return json_decode($content);
    }

    private function rateLimitReset(Response $response): void
    {
        $rateLimitRemaining = $response->header('X-Ratelimit-Remaining');
        if ($rateLimitRemaining && $rateLimitRemaining <= 1) {
            $rateLimitReset = Carbon::createFromTimestamp($response->header('X-Ratelimit-Reset'));
            $difference = $rateLimitReset->diffInSeconds(Carbon::now());
            sleep($difference + 2);
        }
    }
}
