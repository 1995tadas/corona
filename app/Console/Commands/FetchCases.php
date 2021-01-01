<?php

namespace App\Console\Commands;

use App\Services\CaseService;
use App\Services\CountryService;
use App\Services\SummaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class FetchCases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cases:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all cases from api';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $summaryService = new SummaryService();
        $countriesSummary = $summaryService->fetchAndStoreOrUpdateSummary();
        $caseService = new CaseService();
        $lithuanian = false;
        if (App::isLocale('lt')) {
            $countryService = new CountryService();
            $lithuanian = true;
        }

        $this->withProgressBar($countriesSummary, function ($summary) use ($lithuanian, $countryService, $caseService) {
            if (isset($summary['slug'])) {
                if ($lithuanian) {
                    $country = $countryService->getCountry($summary['slug']);
                    $country = $country->lt_country;
                } else {
                    $country = $summary['country'];
                }

                if ($caseService->checkIfEmpty($summary['slug'])) {
                    $cases = $caseService->fetchAndStoreCases($summary['slug']);
                    if ($cases) {
                        $this->newLine();
                        $this->info($country . ' ' . __('cases.saved'));
                    }
                } else {
                    $updated = $caseService->updateCases($summary['slug']);
                    if ($updated) {
                        $this->newLine();
                        $this->info($country . ' ' . __('cases.updated'));
                    }
                }
            }
        });
    }
}
