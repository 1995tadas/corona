<?php

namespace App\Models;

use App\Services\DateTimeService;
use App\Services\ProvinceService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corona extends Model
{
    use HasFactory;

    public $timestamps = [];

    protected $fillable = ['confirmed', 'deaths', 'active', 'date', 'country_id', 'province_id'];

    public function setDateAttribute($value)
    {
        $dateTimeService = new DateTimeService();
        $this->attributes['date'] = $dateTimeService->extractDate($value);
    }

    public function setProvinceIdAttribute($value)
    {
        if (empty($value) || !(isset($value['province']) && isset($value['country_id']))) {
            $this->attributes['province'] = null;
        } else {
            $provinceModel = new ProvinceService();
            $province = $provinceModel->firstOrCreate([
                'province' => $value['province'],
                'country_id' => $value['country_id'],
            ]);
            $this->attributes['province_id'] = $province->id;
        }
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

}
