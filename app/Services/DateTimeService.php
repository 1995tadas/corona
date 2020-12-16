<?php

namespace App\Services;

use Carbon\Carbon;

class DateTimeService
{
    public function convertToISO8601DateTime(string $dateTime): string
    {
        return Carbon::parse($dateTime)->format('Y-m-d\TH:i:s\Z');
    }

    public function addDays(string $dateTime, int $days): string
    {
        return Carbon::parse($dateTime)->addDays($days);
    }

    public function prepareIntervalDates(string $startingDate, string $endingDate = ''): array
    {
        if (!$endingDate) {
            $endingDate = Carbon::today('UTC');
        }

        $startingDate = $this->convertToISO8601DateTime($startingDate);
        $today = $this->convertToISO8601DateTime($endingDate);
        return [
            'from' => $startingDate,
            'to' => $today
        ];
    }
}
