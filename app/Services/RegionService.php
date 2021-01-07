<?php

namespace App\Services;

use App\Models\Region;

class RegionService
{
    public function getRegionsWithSubRegions()
    {
       return Region::with('subRegion')->with('continent')->get();
    }
}
