<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['country', 'slug', 'iso2'];

    public $timestamps = [];

    public function setIso2Attribute($value)
    {
        $lowerCaseIso2 = strtolower($value);
        $this->attributes['iso2'] = $lowerCaseIso2;
        if (App::isLocale('lt')) {
            $countryInLithuanian = __('countries.' . $lowerCaseIso2);
            if ($countryInLithuanian) {
                $this->attributes['lt_country'] = $countryInLithuanian;
            }
        }
    }

    public function allCases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Corona::class)->orderBy('date', 'DESC');
    }

    public function cases(int $provinceId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $query = $this->allCases();
        if ($provinceId) {
            $query->where('province_id', $provinceId);
        }

        return $query;
    }

    public function whereNoProvince($provinceId = null): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        $query = $this->cases($provinceId);
        if (!$provinceId) {
            $query->whereNull('province_id');
        }

        return $query;
    }

    public function provinces(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Province::class);
    }

}
