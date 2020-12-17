<?php

namespace App\Services;

class ArrayService
{
    public function multiArrayKeyCaseChange(array $multidimensionalArray, bool $uppercase = false): array
    {
        if ($uppercase) {
            $case = CASE_UPPER;
        } else {
            $case = CASE_LOWER;
        }

        return array_map(function ($singleArray) use ($case) {
            return array_change_key_case((array)$singleArray, $case);
        }, $multidimensionalArray);
    }

    public function multiArraySelect(array $multidimensionalArray, array $keysToLeave): array
    {
        return array_map(function ($singleArray) use ($keysToLeave) {
            return array_intersect_key((array)$singleArray, array_flip($keysToLeave));
        }, $multidimensionalArray);
    }

    public function multiArrayAddValues(array $multidimensionalArray, array $values): array
    {
        return array_map(function ($singleArray) use ($values) {
            return array_merge($singleArray, $values);
        }, $multidimensionalArray);
    }
}
