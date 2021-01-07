<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    public $timestamps = [];

    protected $fillable = ['continent_id', 'sub_region_id'];

    public function subRegion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubRegion::class, 'sub_region_id', 'id');
    }

    public function continent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Continent::class, 'continent_id', 'id');
    }
}
