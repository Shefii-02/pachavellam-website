<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    use HasFactory;
    protected $table = 'bulletin';
    protected $fillable = [
        'file_name',
        'download',
        'month_year',
        'issue',
        'position',
        'status',
        
    ];
}
