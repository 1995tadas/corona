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

    public function readyForUpdate(): bool
    {
        $record = $this->cronStatus->first();
        if ($record) {
            $dateTimeService = new DateTimeService();
            $updatedDaysAgo = $dateTimeService->passedDays($record->updated);
            if ($updatedDaysAgo != 0) {
                return true;
            }
        }

        return false;
    }

    public function createOrUpdate(): void
    {
        $now = Carbon::now();
        $recordCount = $this->cronStatus->count();
        if ($recordCount === 0) {
            $this->cronStatus->create(['updated' => $now]);
        } else {
            $record = $this->cronStatus->first();
            $record->update(['updated' => $now]);
        }
    }
}
