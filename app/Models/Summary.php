<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = ['total_confirmed', 'new_confirmed', 'total_deaths', 'new_deaths', 'total_recovered', 'new_recovered', 'country_id'];
}
