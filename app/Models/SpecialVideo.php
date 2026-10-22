<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SpecialVideo extends Model
{
    use HasFactory;
    protected $table = 'special_videos';

    protected static function booted(): void
    {
        // the public player address uses this random id, never the YouTube link
        static::creating(function ($m) {
            $m->uid = $m->uid ?: ContentItem::makeUid();
        });
    }

    public function playerUrl(): string
    {
        return url('player/' . $this->uid);
    }
}
