<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_new_user' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /** Status shown to admins; a "sent" OTP past its expiry reads as expired. */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'sent' && $this->expires_at && $this->expires_at->isPast()) {
            return 'expired';
        }
        return $this->status;
    }
}
