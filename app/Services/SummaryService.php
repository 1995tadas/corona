<?php

namespace App\Services;

use App\Models\Summary;

class SummaryService
{
    private const API_QUERY_FOR_SUMMARY = 'summary';

    public function fetchSummaryFromApi(): object
    {
        $requestService = new RequestService('Covid',self::API_QUERY_FOR_SUMMARY );
        return $requestService->MakeRequest();
    }

    public function fetchAndStoreOrUpdateSummary(): array
    {
        $summary = $this->fetchSummaryFromApi();
        if ($summary && isset($summary->Global) && isset($summary->Countries)) {
            $globalCases[] = $summary->Global;
            $countriesCases = $summary->Countries;
            $summaryCases = array_merge($globalCases, $countriesCases);
            $arrayService = new ArrayService();
            $preparedSummary = $arrayService->prepareSummaryArrayForStoring($summaryCases);
            $this->updateOrStore($preparedSummary);
            return $preparedSummary;
        }

        return [];
    }

    public function fetchGlobalSummary()
    {
        return Summary::whereNull('country_id')->first();
    }

    public function fetchSummaryFromDatabaseWithCountryInfo(): \Illuminate\Database\Eloquent\Collection
    {
        return Summary::with('country')->get();
    }

    public function checkIfSummaryExists(): bool
    {
        return Summary::whereNull('country_id')->exists();
    }

    public function fetchAndPrepareSummaryFromDatabase(): array
    {
        $countriesSummary = $this->fetchSummaryFromDatabaseWithCountryInfo()->toArray();
        $selectedFields = ['total_confirmed', 'new_confirmed', 'total_deaths', 'new_deaths'];
        $arrayService = new ArrayService();
        return array_map(function ($countrySummary) use ($arrayService, $selectedFields) {
            if ($countrySummary['country']) {
                $population = $countrySummary['country']['population'];
                $countrySummary = $arrayService->casesPerCapitaForSelectedFields($countrySummary, $selectedFields, $population);
            }

            return $countrySummary;
        }, $countriesSummary);
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

    public function formatSummaryForDiagram(object $summary): array
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
        return $arrayService->formatDataForDiagram($summary, $fields);
    }
}
