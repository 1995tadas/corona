<?php

namespace App\Services;

use App\Models\Summary;

class SummaryService
{
    private const API_QUERY_FOR_SUMMARY = 'summary';

    public function fetchSummaryFromApi(): object
    {
        $requestService = new RequestService();
        return $requestService->performGetRequestCovidApi(self::API_QUERY_FOR_SUMMARY);
    }

    public function fetchAndStoreOrUpdateSummary(): array
    {
        $summary = $this->fetchSummaryFromApi();
        if ($summary && isset($summary->Global) && isset($summary->Countries)) {
            $arrayService = new ArrayService();
            $globalCases[] = $summary->Global;
            $countriesCases = $summary->Countries;
            $summaryCases = array_merge($globalCases, $countriesCases);
            $preparedSummary = $arrayService->prepareCasesArrayForStoring($summaryCases);
            $this->updateOrStore($preparedSummary);
            return $preparedSummary;
        }

        return [];
    }

    public function fetchGlobalSummary()
    {
        return Summary::whereNull('country_id')->first();
    }

    public function fetchSummaryFromDatabaseWithCountry(): \Illuminate\Database\Eloquent\Collection
    {
        return Summary::with('country')->get();
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

    public function takeGlobalSummary(array $summaryArray): array
    {
        foreach ($summaryArray as $record) {
            if (!array_key_exists('country_id', $record) || $record['country_id'] === null) {
                return $record;
            }
        }

        return [];
    }
}
