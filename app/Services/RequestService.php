<?php

namespace App\Services;

class RequestService
{
    private const COVID_API_URL = "https://api.covid19api.com/";
    private const COUNTRIES_API_URL = "https://restcountries.eu/rest/v2/";
    private ApiService $apiService;

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    public function performGetRequestCovidApi(string $query = '', array $parameters = [])
    {
        return $this->apiService->performGetRequest(self::COVID_API_URL, $query, $parameters);
    }

    public function performGetRequestCountriesApi(string $query = '', array $parameters = [])
    {
        return $this->apiService->performGetRequest(self::COUNTRIES_API_URL, $query, $parameters);
    }
}
