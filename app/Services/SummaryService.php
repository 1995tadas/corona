<?php

namespace App\Services;

use App\Models\Summary;

class SummaryService
{
    private $apiService;
    private const API_QUERY_FOR_SUMMARY = 'summary';

    public function __construct()
    {
        $this->apiService = new ApiService();
    }

    public function fetchSummaryFromApi(): object
    {
        return $this->apiService->performGetRequest(self::API_QUERY_FOR_SUMMARY);
    }

    public function fetchAndStoreOrUpdateSummary(): void
    {
        $summary = $this->fetchSummaryFromApi();
        if ($summary && isset($summary->Global) && isset($summary->Countries)) {
            $arrayService = new ArrayService();
            $globalCases[] = $summary->Global;
            $countriesCases = $summary->Countries;
            $summaryCases = array_merge($globalCases, $countriesCases);
            $preparedSummary = $arrayService->prepareCasesArrayForStoring($summaryCases);
            $this->updateOrStore($preparedSummary);
        }
    }

    public function fetchGlobalSummary()
    {
        return Summary::whereNull('country_id')->first();
    }

    public function checkIfSummaryExists()
    {
        return Summary::whereNull('country_id')->exists();
    }

    public function checkIfSummaryNeedStoreOrUpdate(float $daysSinceLastUpdate): bool
    {
        $globalSummary = $this->fetchGlobalSummary();
        if ($globalSummary) {
            $dateAndTimeService = new DateTimeService();
            $passedDays = $dateAndTimeService->passedDays($globalSummary->updated_at);
            return $passedDays >= $daysSinceLastUpdate;
        }

        return true;
    }

    public function updateOrStore(array $cases): void
    {
        $summaryModel = new Summary();
        foreach ($cases as $case) {
            if (!isset($case['country_id'])) {
                $globalSummary = $this->fetchGlobalSummary();
                if ($globalSummary) {
                    $updated = $globalSummary->update($case);
                    $updated ? $globalSummary->touch() : null;
                    continue;
                }

                $country_id = null;
            } else {
                $country_id = $case['country_id'];
            }

            $summaryModel::updateOrCreate(
                ['country_id' => $country_id],
                [
                    'new_confirmed' => $case['new_confirmed'],
                    'total_confirmed' => $case['total_confirmed'],
                    'new_deaths' => $case['new_deaths'],
                    'total_deaths' => $case['total_deaths'],
                    'new_recovered' => $case['new_recovered'],
                    'total_recovered' => $case['total_recovered']
                ]
            );
        }
    }
}
