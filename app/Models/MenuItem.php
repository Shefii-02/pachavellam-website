<?php

namespace App\Models;

use App\Support\Landing;
use Illuminate\Database\Eloquent\Model;

/** One entry of the website's side menu (can have sub items). */
class MenuItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean', 'new_tab' => 'boolean'];

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('id');
    }

    public function url(): ?string
    {
        return Landing::url($this->target_type, $this->target_value, $this->link_url);
    }

    /** Active top level items with their active sub items. */
    public static function tree()
    {
        return self::whereNull('parent_id')->where('status', 1)->orderBy('position')->orderBy('id')
            ->with(['children' => fn ($q) => $q->where('status', 1)])->get();
    }
}
