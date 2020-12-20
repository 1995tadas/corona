<?php

namespace App\Http\Controllers;

use App\Services\CoronaService;
use App\Services\CountryService;

class CoronaController extends Controller
{
    private $coronaService;
    private $countryService;

    public function __construct()
    {
        $this->coronaService = new CoronaService();
        $this->countryService = new CountryService();
    }

    public function show(string $slug)
    {
        $country = $this->countryService->getCountry($slug);
        $provinces = $country->provinces;
        return view('corona.show', compact('country', 'provinces'));
    }

    public function cases(string $slug, int $provinceId = null): \Illuminate\Http\JsonResponse
    {
        if ($this->coronaService->checkIfEmpty($slug)) {
            $cases = $this->coronaService->fetchAndStoreCases($slug);
        } else {
            $cases = $this->coronaService->fetchCasesFromDatabase($slug, $provinceId);
        }

        $finalCases = $this->coronaService->checkIfCasePropertiesAreZeroByPercentage($cases, 0.95);
        return response()->json($finalCases);
    }
}
