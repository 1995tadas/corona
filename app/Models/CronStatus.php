<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronStatus extends Model
{
    use HasFactory;

    protected $fillable = ['updated'];

    public $timestamps = [];
}
