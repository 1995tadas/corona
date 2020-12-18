<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['country', 'slug', 'iso2'];

    public $timestamps = [];

    public function cases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Corona::class)
            ->select('id', 'active', 'confirmed', 'deaths', 'date')
            ->orderBy('date', 'DESC');
    }

}
