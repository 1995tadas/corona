<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['country', 'slug', 'iso2', 'region', 'capital', 'population', 'area'];

    public $timestamps = [];

    public function setIso2Attribute($value)
    {
        $lowerCaseIso2 = strtolower($value);
        $this->attributes['iso2'] = $lowerCaseIso2;
    }

    public function setRegionAttribute($value)
    {
        if (isset($value['continent']) && $value['continent']) {
            $continent = Continent::firstOrCreate([
                'name' => $value['continent'],
            ]);
            $regionData = [];
            if ($continent) {
                $regionData['continent_id'] = $continent->id;
                if (isset($value['sub_region']) && $value['sub_region']) {
                    $subRegion = SubRegion::firstOrCreate([
                        'name' => $value['sub_region'],
                    ]);
                    if ($subRegion) {
                        $regionData['sub_region_id'] = $subRegion->id;
                    }
                }
            }
            if ($regionData) {
                $region = Region::firstOrCreate($regionData);
                if ($region) {
                    $this->attributes['region_id'] = $region->id;
                }
            }
        }
    }

    public function allCases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Corona::class)->orderBy('date', 'DESC');
    }

}
