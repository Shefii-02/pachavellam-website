<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** How much of a class one user has watched (written by the app / web player). */
class ClassWatchLog extends Model
{
    protected $table = 'class_watch_logs';

    protected $guarded = ['id'];

    protected $casts = ['completed' => 'boolean', 'first_watched_at' => 'datetime', 'last_watched_at' => 'datetime'];

    public function libraryClass()
    {
        return $this->belongsTo(LibraryClass::class, 'class_id');
    }
}
