<?php

namespace App\Services;

use App\Models\Corona;
use Carbon\Carbon;

class CaseService
{
    private $apiService;
    private const API_QUERY_FOR_DAY_ONE_TOTAL_CASES = 'total/country/';
    private const API_QUERY_BY_COUNTRY_INTERVAL_CASES = 'country/';

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    public function fetchCasesFromApi(string $countrySlug): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug);
    }

    public function fetchCasesFromApiByInterval(string $countrySlug, array $intervalDates): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_BY_COUNTRY_INTERVAL_CASES . $countrySlug, $intervalDates);
    }

    public function fetchCasesFromDatabase(string $countrySlug): object
    {
        $countryService = new CountryService();
        $country = $countryService->getCountry($countrySlug);
        return $country->allCases;
    }

    public function updateCases(string $countrySlug): bool
    {
        $startingDate = $this->getLatestCountryCaseDate($countrySlug);
        $dateTimeService = new DateTimeService();
        if ($startingDate && $countrySlug) {
            $endingDate = Carbon::today()->toIso8601String();
            $intervalComparison = $dateTimeService->compareTwoDates($dateTimeService->addDays($startingDate, 2), '<', $endingDate);
            if ($intervalComparison) {
                $startingDate = $dateTimeService->addDays($startingDate, 1);
                $intervalDates = $dateTimeService->prepareIntervalDates($startingDate, $endingDate);
                $newCases = $this->fetchCasesFromApiByInterval($countrySlug, $intervalDates);
                if ($newCases) {
                    $arrayService = new ArrayService();
                    $preparedCases = $arrayService->prepareCasesArrayForStoring($newCases, $countrySlug);
                    $this->store($preparedCases);
                    return true;
                }
            }
        }

        return false;
    }

    public function checkIfEmpty(string $slug): bool
    {
        $countryService = new CountryService();
        $country = $countryService->getCountry($slug);
        return Corona::where('country_id', $country->id)->get()->isEmpty();
    }

    public function fetchAndStoreCases(string $slug): object
    {
        $cases = $this->fetchCasesFromApi($slug);
        if ($cases) {
            $arrayService = new ArrayService();
            $cases = $arrayService->prepareCasesArrayForStoring($cases, $slug);
            $this->store($cases);
            return $this->fetchCasesFromDatabase($slug);
        }

        return collect([]);
    }

    public function store(array $cases): void
    {
        $countryModel = new Corona();
        foreach ($cases as $case) {
            $countryModel->create($case);
        }
    }

    public function getLatestCountryCaseDate(string $slug): string
    {
        $countryService = new CountryService();
        $country = $countryService->getCountry($slug);
        $cases = $country->allCases;
        $latestCase = $cases->first();
        return $latestCase->date;
    }
}
