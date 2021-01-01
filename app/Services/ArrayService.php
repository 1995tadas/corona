<?php

namespace App\Services;

use Illuminate\Support\Str;

class ArrayService
{
    public function multiArrayKeysToSnakeCase(array $multidimensionalArray): array
    {
        return array_map(function ($insideArray) {
            gettype($insideArray) === 'object' ? $insideArray = (array)$insideArray : null;
            foreach ($insideArray as $index => $value) {
                unset($insideArray[$index]);
                $insideArray[Str::snake($index)] = $value;
            }

            return $insideArray;
        }, $multidimensionalArray);
    }

    public function multiArrayKeyCaseChange(array $multidimensionalArray): array
    {
        return array_map(function ($insideArray) {
            gettype($insideArray) === 'object' ? $insideArray = (array)$insideArray : null;
            return array_change_key_case($insideArray);
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

    public function multiArrayAddCountryId(array $multidimensionalArray, int $countryId = null): array
    {
        $countryService = new CountryService();

        return array_map(function ($singleArray) use ($countryId, $countryService) {
            if (isset($singleArray['slug'])) {
                $countryId = $countryService->getCountry($singleArray['slug'])->id;
            }

            if ($countryId) {
                $singleArray['country_id'] = $countryId;
            }

            return $singleArray;
        }, $multidimensionalArray);
    }

    public function prepareCasesArrayForStoring(array $cases, string $slug = ''): array
    {
        $countryService = new CountryService();
        if ($slug) {
            $countryId = $countryService->getCountry($slug)->id;
        }

        $cases = $this->multiArrayKeysToSnakeCase($cases);
        $cases = $this->multiArrayAddCountryId($cases, $countryId ?? null);
        return $cases;
    }
}
