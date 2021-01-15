<?php

namespace App\Console\Commands;

use App\Services\CronService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CasesLastUpdated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cases:needsUpdate {--update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if cases needs to be updated (add --update option if you want update after checking)';

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
        $cronService = new CronService();
        $readyForUpdate = $cronService->readyForUpdate();
        if ($readyForUpdate) {
            if ($this->option('update') == true) {
                Artisan::call('cases:fetch');
            }
        } else {
            $this->line(__('commands.no_update'));
        }
    }
}
