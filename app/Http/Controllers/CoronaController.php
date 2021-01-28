<?php

namespace App\Http\Controllers;

use App\Services\CaseService;
use App\Services\CountryService;
use App\Services\DateTimeService;
use App\Services\RegionService;
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
        $countriesSummary = $summaryService->fetchAndPrepareSummaryFromDatabase();
        $globalSummary = $summaryService->takeGlobalSummary($countriesSummary);
        $lastUpdated = '';
        if ($globalSummary) {
            $dateTimeService = new DateTimeService();
            $lastUpdated = $dateTimeService->extractDate($globalSummary['updated_at'], true);
        }

        $caseService = new CaseService();
        $allCases = $caseService->getAllCases();
        $allCases = $this->caseService->formatCasesForDiagram($allCases);
        $regionService = new RegionService();
        $regions = $regionService->getRegionsWithSubRegions();
        return view('corona.index', compact('globalSummary', 'lastUpdated', 'countriesSummary', 'regions', 'allCases'));
    }

    public function show(string $slug)
    {
        $country = $this->countryService->getCountry($slug);
        return view('corona.show', compact('country'));
    }

    public function cases(string $slug): \Illuminate\Http\JsonResponse
    {
        $cases = $this->caseService->fetchCasesFromDatabase($slug);
        $formattedCases = $this->caseService->formatCasesForDiagram($cases);
        foreach ($formattedCases as $index => $case) {
            $formattedCases[$index] = array_reverse($case);
        }

        return response()->json(['formatted' => $formattedCases, 'raw' => $cases]);
    }
}
