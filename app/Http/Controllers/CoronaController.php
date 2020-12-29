<?php

namespace App\Http\Controllers;

use App\Services\CaseService;
use App\Services\CountryService;
use App\Services\DateTimeService;
use App\Services\SummaryService;

class CoronaController extends Controller
{
    private $caseService;
    private $countryService;

    public function __construct()
    {
        $this->caseService = new CaseService();
        $this->countryService = new CountryService();
    }

    public function index()
    {
        $summaryService = new SummaryService();
        $needUpdateOrStore = $summaryService->checkIfSummaryNeedStoreOrUpdate(1);
        if ($needUpdateOrStore) {
            $countriesSummary = $summaryService->fetchAndStoreOrUpdateSummary();
        }

        if (!$needUpdateOrStore || !$countriesSummary) {
            $countriesSummary = $summaryService->fetchSummaryFromDatabaseWithCountry()->toArray();
        }

        $globalSummary = $summaryService->takeGlobalSummary($countriesSummary);
        $lastUpdated = '';
        if ($globalSummary) {
            $dateTimeService = new DateTimeService();
            $lastUpdated = $dateTimeService->ifNoDateSetToCurrentAndExtract(
                isset($globalSummary['updated_at']) ? $globalSummary['updated_at'] : '');
        }

        return view('corona.index', compact('globalSummary', 'lastUpdated', 'countriesSummary'));
    }

    public function show(string $slug)
    {
        $country = $this->countryService->getCountry($slug);
        $provinces = $country->provinces;
        return view('corona.show', compact('country', 'provinces'));
    }

    public function cases(string $slug, int $provinceId = null): \Illuminate\Http\JsonResponse
    {
        if ($this->caseService->checkIfEmpty($slug)) {
            $cases = $this->caseService->fetchAndStoreCases($slug);
        } else {
            $cases = $this->caseService->fetchCasesFromDatabase($slug, $provinceId);
        }

        $finalCases = $this->caseService->checkIfCasePropertiesAreZeroByPercentage($cases, 0.95);
        return response()->json($finalCases);
    }
}
