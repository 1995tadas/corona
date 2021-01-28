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

    public function formatNumbers(array $array): array
    {
        return array_map(function ($value) {
            if (is_numeric($value)) {
                return number_format($value, 0, "", ' ');
            }

            return $value;
        }, $array);
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

    public function multiArrayCountNewCases(array $multidimensionalArray, string $slug = '', bool $update = false): array
    {
        if ($update) {
            $countryService = new CountryService();
            $country = $countryService->getCountry($slug);
            $lastCase = $country->lastCase->first();
            $lastDayCases = [
                'new_confirmed' => $lastCase->confirmed,
                'new_recovered' => $lastCase->recovered,
                'new_active' => $lastCase->active,
                'new_deaths' => $lastCase->deaths];
        } else {
            $lastDayCases = [
                'new_confirmed' => 0,
                'new_recovered' => 0,
                'new_active' => 0,
                'new_deaths' => 0];
        }

        foreach ($multidimensionalArray as $index => $array) {
            if (!isset($array['new_confirmed']) && !isset($array['new_recovered']) && !isset($array['new_deaths'])) {
                $multidimensionalArray[$index]['new_confirmed'] = $array['confirmed'] - $lastDayCases['new_confirmed'];
                $multidimensionalArray[$index]['new_recovered'] = $array['recovered'] - $lastDayCases['new_recovered'];
                $multidimensionalArray[$index]['new_active'] = $array['active'] - $lastDayCases['new_active'];
                $multidimensionalArray[$index]['new_deaths'] = $array['deaths'] - $lastDayCases['new_deaths'];

                $lastDayCases['new_confirmed'] = $array['confirmed'];
                $lastDayCases['new_recovered'] = $array['recovered'];
                $lastDayCases['new_active'] = $array['active'];
                $lastDayCases['new_deaths'] = $array['deaths'];
            }
        }

        return $multidimensionalArray;
    }

    public function prepareCasesArrayForStoring(array $cases, string $slug = '', bool $update = false): array
    {
        $prepared = $this->prepareSummaryArrayForStoring($cases, $slug);
        return $this->multiArrayCountNewCases($prepared, $slug, $update);
    }

    public function prepareSummaryArrayForStoring(array $cases, string $slug = ''): array
    {
        $countryService = new CountryService();
        if ($slug) {
            $countryId = $countryService->getCountry($slug)->id;
        }

        $cases = $this->multiArrayKeysToSnakeCase($cases);
        $cases = $this->multiArrayAddCountryId($cases, $countryId ?? null);
        return $cases;
    }

    public function casesPerCapitaForSelectedFields(array $array, array $selectedFields, int $population): array
    {
        $mathService = new MathService();
        foreach ($selectedFields as $field) {
            $array[$field . '_per_capita'] = $mathService->perCapita($array[$field], $population);
        }

        return $array;
    }

    public function formatDataForDiagram(object $object, array $fields): array
    {
        $formattedData = array_flip($fields);
        foreach ($formattedData as $index => $value) {
            $formattedData[$index] = [];
        }

        foreach ($object as $data) {
            foreach ($formattedData as $index => $value) {
                $formattedData[$index][] = $data->{$index};
            }
        }

        return $formattedData;
    }
}
