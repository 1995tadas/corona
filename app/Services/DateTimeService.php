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
            $endingDate = $this->extractDate(Carbon::today());
        }

        return [
            'from' => $startingDate,
            'to' => $endingDate
        ];
    }

    public function extractDate(string $timestamp, bool $time = false): string
    {
        $format = 'Y-m-d';
        $time ? $format .= ' H:i' : null;

        return Carbon::parse($timestamp)->format($format);
    }

    public function passedDays(string $date): int
    {
        $pastDate = Carbon::parse($date);
        $now = Carbon::now();
        return $pastDate->diffInDays($now);
    }

    public function ifNoDateSetToCurrentAndExtract(string $timestamp): string
    {
        if (!$timestamp) {
            $timestamp = Carbon::now();
        }

        return $this->extractDate($timestamp, true);
    }
}
