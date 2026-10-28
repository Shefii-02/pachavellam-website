<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One signed in device of a person: a browser on the website or a phone with the app. */
class DeviceSession extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['last_active_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeLive($q)
    {
        return $q->whereNull('revoked_at');
    }

    public function label(): string
    {
        $os = $this->channel === 'app' ? ($this->platform === 'ios' ? 'iPhone / iPad app' : ($this->platform === 'android' ? 'Android app' : 'Mobile app')) : 'Website';

        return trim(($this->device_name ?: $os) . ($this->device_name && $this->channel === 'app' ? '' : ''));
    }
}
