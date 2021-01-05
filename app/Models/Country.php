<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['country', 'slug', 'iso2', 'region', 'sub_region', 'capital', 'population', 'area'];

    public $timestamps = [];

    public function setIso2Attribute($value)
    {
        $lowerCaseIso2 = strtolower($value);
        $this->attributes['iso2'] = $lowerCaseIso2;
    }

    public function setRegionAttribute($value)
    {
        $region = Region::firstOrCreate([
            'name' => $value
        ]);

        if ($region) {
            $this->attributes['region_id'] = $region->id;
        }
    }

    public function setSubRegionAttribute($value)
    {
        $subRegion = SubRegion::firstOrCreate([
            'name' => $value
        ]);

        if ($subRegion) {
            $this->attributes['sub_region_id'] = $subRegion->id;
        }
    }

    public function allCases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Corona::class)->orderBy('date', 'DESC');
    }

}
