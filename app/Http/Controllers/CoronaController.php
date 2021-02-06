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
        $allCases = $caseService->getAll();
        $allCases = $this->caseService->formatDataForDiagram($allCases);
        $regionService = new RegionService();
        $regions = $regionService->getRegionsWithSubRegions();
        return view('corona.index', compact('globalSummary', 'lastUpdated', 'countriesSummary', 'regions', 'allCases'));
    }

    public function show(string $slug)
    {
        $country = $this->countryService->getSingle($slug);
        return view('corona.show', compact('country'));
    }

    public function cases(string $slug): \Illuminate\Http\JsonResponse
    {
        $cases = $this->caseService->fetchFromDatabase($slug);
        $formattedCases = $this->caseService->formatDataForDiagram($cases);
        foreach ($formattedCases as $index => $case) {
            $formattedCases[$index] = array_reverse($case);
        }

        return response()->json(['formatted' => $formattedCases, 'raw' => $cases]);
    }
}
