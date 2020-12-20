<?php

namespace App\Services;

use App\Models\Corona;
use App\Models\Country;
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

    public function fetchCasesFromApi(string $countrySlug): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_FOR_DAY_ONE_TOTAL_CASES . $countrySlug);
    }

    public function fetchCasesFromApiByInterval(string $countrySlug, array $intervalDates): array
    {
        return $this->apiService->performGetRequest(self::API_QUERY_BY_COUNTRY_INTERVAL_CASES . $countrySlug, $intervalDates);
    }

    public function fetchCasesFromDatabase(string $countrySlug, int $provinceId = null): object
    {
        $countryService = new CountryService();
        $country = $countryService->getCountry($countrySlug);
        $cases = $country->cases($provinceId)->get();
        if (!$cases->isEmpty()) {
            $latestCaseDate = $this->getLatestCountryCaseDate($country);
            $this->updateCases($latestCaseDate, $countrySlug);
            $cases = $country->whereNoProvince($provinceId)->get();
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
                $newCases = $this->fetchCasesFromApiByInterval($countrySlug, $intervalDates);
                $this->prepareAndStore($newCases, $countrySlug);
                return true;
            }
        }

        return false;
    }

    public function deleteCasesIfUnfinished(Corona $latestCase): bool
    {
        $latestDateCases = Corona::where('date', $latestCase->date)
            ->where('country_id', $latestCase->country_id)
        ->whereNotNull('province_id');
        $lastDateCasesCount = $latestDateCases->count();
        $provincesCount = Country::find($latestCase->country_id)->provinces->count();

        if ($provincesCount > 1 && $provincesCount > $lastDateCasesCount) {
            foreach ($latestDateCases->get() as $latestDateCase) {
                $latestDateCase->delete();
            }

            return true;
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
            $this->prepareAndStore($cases, $slug);
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

    public function prepareAndStore(array $cases, string $slug): void
    {
        $arrayService = new ArrayService();
        $countryService = new CountryService();
        $country = $countryService->getCountry($slug);
        $cases = $arrayService->multiArraySelect($cases, ['Deaths', 'Active', 'Confirmed', 'Date', 'Province']);
        $cases = $arrayService->multiArrayKeyCaseChange($cases);
        $cases = $arrayService->multiArrayAddCountryIdToProvinces($cases, $country->id);
        $cases = $arrayService->multiArrayAddValues($cases, ['country_id' => $country->id]);
        $this->store($cases);
    }

    public function getLatestCountryCaseDate(Country $country): string
    {
        $cases = $country->allCases;
        $latestCase = $cases->first();
        if ($latestCase) {
            $deleted = $this->deleteCasesIfUnfinished($latestCase);
            if ($deleted) {
                $latestCase = $cases->first();
            }

            return $latestCase->date;
        }

        return '';
    }

    public function checkIfCasePropertiesAreZeroByPercentage(object $cases, float $percentage): object
    {
        if ($cases->first()) {
            $emptyCount = 0;
            foreach ($cases as $case) {
                if ($case->confirmed == 0 && $case->deaths == 0 && $case->active == 0) {
                    $emptyCount++;
                }
            }

            if (($cases->count() * $percentage) < $emptyCount) {
                return collect([]);
            }
        }

        return $cases;
    }
}
