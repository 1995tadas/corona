<?php

namespace App\Services;

use App\Models\Country;

class CountryService
{
    private $apiService;
    private const API_QUERY_FOR_COUNTRIES = 'countries';

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    public function getCountries(): object
    {
        $countriesFromDatabase = $this->fetchCountriesFromDatabase();
        if (!$countriesFromDatabase->isEmpty()) {
            return $countriesFromDatabase;
        }

        return (object)$this->fetchCountriesFromApiAndStore();
    }

    public function getCountry(string $slug): \App\Models\Country
    {
        if (!$this->checkIfCountryExistsInDatabase($slug)) {
            $this->fetchCountriesFromApiAndStore();
        }

        return $this->fetchCountryFromDatabase($slug);
    }

    public function fetchCountriesFromApi(): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_FOR_COUNTRIES);
    }

    public function fetchCountriesFromApiAndStore(): array
    {
        $countriesFromApi = $this->fetchCountriesFromApi();
        if ($countriesFromApi) {
            $arrayService = new ArrayService();
            $lowercaseCountries = $arrayService->multiArrayKeyCaseChange($countriesFromApi);
            $this->storeCountries($lowercaseCountries);
            return $lowercaseCountries;
        }

        return [];
    }

    public function fetchCountriesFromDatabase(): \Illuminate\Database\Eloquent\Collection
    {
        return Country::all(['country', 'slug', 'iso2']);
    }

    public function storeCountries(array $countries): bool
    {
        $countryModel = new Country();
        return $countryModel->insert($countries);
    }

    public function checkIfCountryExistsInDatabase(string $slug): bool
    {
        return Country::where('slug', $slug)->exists();
    }

    public function fetchCountryFromDatabase(string $slug): \App\Models\Country
    {
        return Country::where('slug', $slug)->firstOrFail();
    }

}
