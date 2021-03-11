<?php

namespace App\Services;

class RequestService
{
    private const COVID_API_URL = "https://api.covid19api.com/";
    private const COUNTRIES_API_URL = "https://restcountries.eu/rest/v2/";

    private $api;
    private $query = '';
    private $parameters = [];

    private $apiService;

    public function __construct(string $api, string $query = '', array $parameters = [])
    {
        if (empty($api)) {
            throw new \InvalidArgumentException('Api string can\'t be empty !');
        }

        $this->api = $api;
        if (!empty($query)) {
            $this->query = $query;
        }

        if (!empty($parameters)) {
            $this->parameters = $parameters;
        }
    }

    public function MakeRequest()
    {
        $this->apiService = New ApiService();
        return $this->{ucfirst($this->api)}($this->query, $this->parameters);
    }

    private function Covid(string $query = '', array $parameters = [])
    {
        return $this->apiService->performGetRequest(self::COVID_API_URL, $query, $parameters);
    }

    private function Country(string $query = '', array $parameters = [])
    {
        return $this->apiService->performGetRequest(self::COUNTRIES_API_URL, $query, $parameters);
    }
}
