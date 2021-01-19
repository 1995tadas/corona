<?php

namespace App\Services;

class MathService
{
    public function perCapita(int $number, int $population): float
    {
        return round(($number / $population * 100000), 2);
    }
}
