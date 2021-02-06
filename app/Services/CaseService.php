<?php

namespace App\Services;

use App\Models\Corona;
use Carbon\Carbon;

class CaseService
{
    private const API_QUERY_FOR_DAY_ONE_TOTAL_CASES = 'total/country/';

    public function fetchFromApi(string $countrySlug): array
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCovidApi(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug);
    }

    public function fetchFromApiByInterval(string $countrySlug, array $intervalDates): array
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCovidApi(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug, $intervalDates);
    }

    public function fetchFromDatabase(string $countrySlug): object
    {
        $countryService = new CountryService();
        $country = $countryService->getSingle($countrySlug);
        return $country->allCases;
    }

    public function updateCases(string $countrySlug): bool
    {
        $startingDate = $this->getLatestCountryDate($countrySlug);
        $dateTimeService = new DateTimeService();
        if ($startingDate && $countrySlug) {
            $endingDate = Carbon::today()->toIso8601String();
            $startingDate = $dateTimeService->addDays($startingDate, 1);
            $needsUpdate = $dateTimeService->compareTwoDates($startingDate, '<', $endingDate, true);
            if ($needsUpdate) {
                $intervalDates = $dateTimeService->prepareIntervalDates($startingDate, $endingDate);
                $newCases = $this->fetchFromApiByInterval($countrySlug, $intervalDates);
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
        $country = $countryService->getSingle($slug);
        return Corona::where('country_id', $country->id)->isEmpty();
    }

    public function fetchAndStore(string $slug): object
    {
        $cases = $this->fetchFromApi($slug);
        if ($cases) {
            $arrayService = new ArrayService();
            $cases = $arrayService->prepareCasesArrayForStoring($cases, $slug);
            $this->store($cases);
            return $this->fetchFromDatabase($slug);
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

    public function getLatestCountryDate(string $slug): string
    {
        $countryService = new CountryService();
        $country = $countryService->getSingle($slug);
        $cases = $country->allCases;
        $latestCase = $cases->first();
        return $latestCase->date;
    }

    public function getAll(): object
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

    public function formatDataForDiagram(object $cases): array
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
