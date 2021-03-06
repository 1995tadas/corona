<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubRegion extends Model
{
    use HasFactory;

    public $timestamps = [];

    protected $fillable = ['name', 'region_id'];
}
