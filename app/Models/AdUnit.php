<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdUnit extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['enabled' => 'boolean', 'pages' => 'array', 'hide_for_paid' => 'boolean'];

    /** Does this unit show on a page (page key from App\Support\AdPages)? */
    public function onPage(string $page): bool
    {
        return $this->enabled && in_array($page, (array) $this->pages, true);
    }
}
