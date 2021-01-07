<?php

namespace App\Services;

use App\Models\Country;

class CountryService
{
    private const API_QUERY_FOR_COUNTRIES = 'countries';
    private const API_QUERY_FOR_COUNTRIES_INFO = 'all';

    public function fetchCountriesFromApi(): array
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCovidApi(self::API_QUERY_FOR_COUNTRIES);
    }

    public function fetchCountriesInfoFromApi(): array
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCountriesApi(self::API_QUERY_FOR_COUNTRIES_INFO);
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
        if ($this->checkIfCountryTableIsEmpty()) {
            $this->fetchCountriesFromApiAndStore();
        }

        return $this->fetchCountryFromDatabase($slug);
    }

    public function addAdditionalInfoToCountriesArray(array $countries): array
    {
        $countriesInfoFromApi = $this->fetchCountriesInfoFromApi();
        if ($countriesInfoFromApi) {
            foreach ($countriesInfoFromApi as $countryInfo) {
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

    public function fetchCountriesFromApiAndStore(): array
    {
        $countriesFromApi = $this->fetchCountriesFromApi();
        $countriesFromApi = $this->addAdditionalInfoToCountriesArray($countriesFromApi);
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

    public function storeCountries(array $countries): void
    {
        $countryModel = new Country();
        foreach ($countries as $country) {
            $countryModel->create($country);
        }
    }

    public function checkIfCountryExistsInDatabase(string $slug): bool
    {
        return Country::where('slug', $slug)->exists();
    }

    public function checkIfCountryTableIsEmpty(): bool
    {
        return Country::all()->isEmpty();
    }

    public function fetchCountryFromDatabase(string $slug): \App\Models\Country
    {
        return Country::where('slug', $slug)->firstOrFail();
    }
}
