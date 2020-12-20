<?php

namespace App\Services;

use App\Models\Province;

class ProvinceService
{
    public function firstOrCreate(array $province)
    {
        $provinceModel = new Province();
        return $provinceModel->firstOrCreate($province);
    }
}
