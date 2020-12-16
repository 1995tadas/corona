<?php

namespace App\Services;

use App\Models\Corona;
use Carbon\Carbon;

class CoronaService
{
    private $apiService;
    private const API_QUERY_FOR_DAY_ONE_TOTAL_CASES = 'dayone/country/';
    private const API_QUERY_BY_COUNTRY_INTERVAL_CASES = 'country/';

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    public function fetchTotalCasesFromApi(string $countrySlug): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug);
    }

    public function fetchCasesByInterval(string $countrySlug, array $intervalDates): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_BY_COUNTRY_INTERVAL_CASES . $countrySlug, $intervalDates);
    }

    public function fetchTotalCasesFromDatabase(string $countrySlug): object
    {
        $countryService = new CountryService();
        $cases = $countryService->getCountry($countrySlug)->cases;
        if (!$cases->isEmpty()) {
            $latestCaseDate = $this->getLatestCaseDate($cases);
            $updated = $this->updateCases($latestCaseDate, $countrySlug);
            if ($updated) {
                $newCases = $this->fetchUpdatedCases($latestCaseDate, $countrySlug);
                $cases = $cases->merge($newCases);
            }
        }

        return $cases;
    }

    public function updateCases(string $startingDate, string $countrySlug): bool
    {
        $dateTimeService = new DateTimeService();
        if ($startingDate && $countrySlug) {
            $startingDate = $dateTimeService->addDays($startingDate, 1);
            $endingDate = Carbon::today('UTC');
            if ($startingDate != $endingDate) {
                $intervalDates = $dateTimeService->prepareIntervalDates($startingDate, $endingDate);
                $newCases = $this->fetchCasesByInterval($countrySlug, $intervalDates);
                $this->prepareAndStore($newCases, $countrySlug);
                return true;
            }
        }

        return false;
    }

    public function fetchUpdatedCases(string $latestCaseDate, string $countrySlug): object
    {
        return Corona::whereDate('date', '>', $latestCaseDate)->
        whereHas('country', function ($query) use ($countrySlug) {
            return $query->where('slug', $countrySlug);
        })->get();
    }

    public function fetchAndStoreCases(string $slug): object
    {
        $cases = $this->fetchTotalCasesFromApi($slug);
        if ($cases) {
            $this->prepareAndStore($cases, $slug);
            return $this->fetchTotalCasesFromDatabase($slug);
        }

        return (object)[];
    }

    public function store(array $cases): void
    {
        $countryModel = new Corona();
        foreach ($cases as $case) {
            $countryModel->create($case);
        }
    }

    public function prepareAndStore(array $cases, string $slug): void
    {
        $arrayService = new ArrayService();
        $countryService = new CountryService();
        $country = $countryService->getCountry($slug);
        $cases = $arrayService->multiArraySelect($cases, ['Deaths', 'Active', 'Confirmed', 'Date']);
        $cases = $arrayService->multiArrayKeyCaseChange($cases);
        $cases = $arrayService->multiArrayAddValues($cases, ['country_id' => $country->id]);
        $this->store($cases);
    }

    public function getLatestCaseDate(object $cases): string
    {
        $latestCase = $cases->first();
        if ($latestCase) {
            return $latestCase->date;
        }

        return '';
    }
}
