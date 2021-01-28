<?php

namespace App\Services;

use App\Models\Corona;
use Carbon\Carbon;

class CaseService
{
    private const API_QUERY_FOR_DAY_ONE_TOTAL_CASES = 'total/country/';

    public function fetchCasesFromApi(string $countrySlug): array
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCovidApi(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug);
    }

    public function fetchCasesFromApiByInterval(string $countrySlug, array $intervalDates): array
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCovidApi(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug, $intervalDates);
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
            $startingDate = $dateTimeService->addDays($startingDate, 1);
            $needsUpdate = $dateTimeService->compareTwoDates($startingDate, '<', $endingDate, true);
            if ($needsUpdate) {
                $intervalDates = $dateTimeService->prepareIntervalDates($startingDate, $endingDate);
                $newCases = $this->fetchCasesFromApiByInterval($countrySlug, $intervalDates);
                if ($newCases) {
                    $arrayService = new ArrayService();
                    $preparedCases = $arrayService->prepareCasesArrayForStoring($newCases, $countrySlug, true);
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

    public function getAllCases(): object
    {
        return Corona::selectRaw(
        'SUM(confirmed) AS confirmed,
         SUM(new_confirmed) AS new_confirmed,
         SUM(deaths) AS deaths,
         SUM(new_deaths) AS new_deaths,
         SUM(active) AS active,
         SUM(new_active) AS new_active,
         SUM(recovered) AS recovered,
         SUM(new_recovered) AS new_recovered,
         date')
            ->groupBy('date')->get();
    }

    public function formatCasesForDiagram(object $cases): array
    {
        $fields = [
            'active',
            'new_active',
            'confirmed',
            'new_confirmed',
            'recovered',
            'new_recovered',
            'deaths',
            'new_deaths',
            'date'
        ];
        $arrayService = new ArrayService();
        return $arrayService->formatDataForDiagram($cases, $fields);
    }
}
