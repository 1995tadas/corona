<?php

namespace App\Services;

use App\Models\CronStatus;
use Carbon\Carbon;

class CronService
{
    private $cronStatus;

    public function __construct()
    {
        $this->cronStatus = new CronStatus();
    }

    public function casesReadyForUpdate(): bool
    {
        $record = $this->cronStatus->where('type', 'cases_update')->first();
        if (!$record) {
            return true;
        }

        $dateTimeService = new DateTimeService();
        $updatedDaysAgo = $dateTimeService->passedDays($record->updated);
        if ($updatedDaysAgo != 0) {
            return true;
        }
    }

    public function createOrUpdateCases(): void
    {
        $now = Carbon::now();
        $recordCount = $this->cronStatus->where('type', 'cases_update')->count();
        if ($recordCount === 0) {
            $this->cronStatus->create([
                'type' => 'cases_update',
                'updated' => $now
            ]);
        } else {
            $record = $this->cronStatus->where('type', 'cases_update')->first();
            $record->update(['updated' => $now]);
        }
    }
}
