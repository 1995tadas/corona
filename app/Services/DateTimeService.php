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

        return Carbon::parse($timestamp)->setTimezone(config('app.timezone'))->format($format);
    }

    public function passedDays(string $date): int
    {
        $pastDate = Carbon::parse($date);
        $now = Carbon::now();
        return $pastDate->diffInDays($now);
    }

    public function compareTwoDates(string $firstDate, string $operator, string $secondDate): bool
    {
        $first = Carbon::parse($firstDate);
        $second = Carbon::parse($secondDate);
        switch ($operator) {
            case '<':
                return $first->lessThan($second);
            case '>':
                return $first->greaterThan($second);
            case '=':
                return $first->equalTo($second);
            case '!=':
                return $first->notEqualTo($second);
            case '>=':
                return $first->greaterThanOrEqualTo($second);
            case '<=':
                return $first->lessThanOrEqualTo($second);
            default:
                return false;
        }
    }
}
