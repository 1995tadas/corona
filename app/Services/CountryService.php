<?php

namespace App\Services;

use App\Models\Country;

class CountryService
{
    private const API_QUERY_FOR_COUNTRIES = 'countries';
    private const API_QUERY_FOR_COUNTRIES_INFO = 'all';

    public function fetchFromApi(): array
    {
        $requestService = new RequestService('covid',self::API_QUERY_FOR_COUNTRIES);
        return $requestService->MakeRequest();
    }

    public function fetchDetailsFromApi(): array
    {
        $requestService = new RequestService('country',self::API_QUERY_FOR_COUNTRIES_INFO);
        return $requestService->MakeRequest();
    }

    public function getAll(): object
    {
        $countriesFromDatabase = $this->fetchAllFromDatabase();
        if (!$countriesFromDatabase->isEmpty()) {
            return $countriesFromDatabase;
        }

        return (object)$this->fetchFromApiAndStore();
    }

    public function getSingle(string $slug): \App\Models\Country
    {
        if ($this->checkIfEmptyTable()) {
            $this->fetchFromApiAndStore();
        }

        return $this->fetchFromDatabase($slug);
    }

    public function addAdditionalCountyDetailsToArray(array $countries): array
    {
        $countryDetailsFromApi = $this->fetchDetailsFromApi();
        if ($countryDetailsFromApi) {
            foreach ($countryDetailsFromApi as $countryInfo) {
                foreach ($countries as $index => $country) {
                    if ($countryInfo->alpha2Code === $country->ISO2) {
                        $countries[$index]->region['continent'] = $countryInfo->region;
                        $countries[$index]->region['sub_region'] = $countryInfo->subregion;
                        $countries[$index]->capital = $countryInfo->capital;
                        $countries[$index]->population = $countryInfo->population;
                        $countries[$index]->area = $countryInfo->area;
                        break;
                    }
                }
            }

            return $countries;
        }

        return [];
    }

    public function fetchFromApiAndStore(): array
    {
        $countriesFromApi = $this->fetchFromApi();
        $countriesFromApi = $this->addAdditionalCountyDetailsToArray($countriesFromApi);
        if ($countriesFromApi) {
            $arrayService = new ArrayService();
            $lowercaseCountries = $arrayService->multiArrayKeyCaseChange($countriesFromApi);
            $this->store($lowercaseCountries);
            return $lowercaseCountries;
        }

        return [];
    }

    public function fetchAllFromDatabase(): \Illuminate\Database\Eloquent\Collection
    {
        return Country::all(['country', 'slug', 'iso2']);
    }

    public function store(array $countries): void
    {
        $countryModel = new Country();
        foreach ($countries as $country) {
            $countryModel->create($country);
        }
    }

    public function checkIfExistsInDatabase(string $slug): bool
    {
        return Country::where('slug', $slug)->exists();
    }

    public function checkIfEmptyTable(): bool
    {
        return Country::all()->isEmpty();
    }

    public function fetchFromDatabase(string $slug): \App\Models\Country
    {
        return Country::where('slug', $slug)->firstOrFail();
    }
}
