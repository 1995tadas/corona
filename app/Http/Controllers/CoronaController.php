<?php

namespace App\Http\Controllers;

use App\Services\CoronaService;

class CoronaController extends Controller
{
    private $coronaService;

    public function __construct()
    {
        $this->coronaService = new CoronaService();
    }

    public function show(string $slug)
    {
        $cases = $this->coronaService->fetchTotalCasesFromDatabase($slug);
        if($cases->isEmpty()){
            $cases = $this->coronaService->fetchAndStoreCases($slug);
        }

        return view('corona.show', compact('cases'));
    }
}
