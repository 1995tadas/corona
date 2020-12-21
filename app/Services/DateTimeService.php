<?php

namespace App\Services;

use Carbon\Carbon;

class DateTimeService
{

    public function addDays(string $dateTime, int $days): string
    {
        return Carbon::parse($dateTime)->addDays($days);
    }

    public function prepareIntervalDates(string $startingDate, string $endingDate = ''): array
    {
        if (!$endingDate) {
            $endingDate = $this->extractDate(Carbon::today('UTC'));
        }

        return [
            'from' => $startingDate,
            'to' => $endingDate
        ];
    }

    public function extractDate(string $timestamp): string
    {
        return Carbon::parse($timestamp)->format('Y-m-d');
    }

    public function passedDays(string $date): int
    {
        $pastDate = Carbon::parse($date);
        $now = Carbon::now();
        return $pastDate->diffInDays($now);
    }
}
