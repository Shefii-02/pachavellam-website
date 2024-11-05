<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyBucket extends Model
{
    use HasFactory;
    protected $table = 'psc_daily_buckets';
}
