<?php

namespace App\Models;

use App\Services\DateTimeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Corona extends Model
{
    use HasFactory;

    public $timestamps = [];

    protected $fillable = ['confirmed', 'deaths', 'active', 'date', 'country_id'];

    public function setDateAttribute($value)
    {
        $dateTimeService = new DateTimeService();
        $this->attributes['date'] = $dateTimeService->extractDate($value);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

}
