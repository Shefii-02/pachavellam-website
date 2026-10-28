<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A custom page written in the panel (/p/{slug}) or a prebuilt page (an address the website already has). */
class SitePage extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = ['status' => 'boolean'];

    public function isCustom(): bool
    {
        return $this->kind === 'custom';
    }

    public function url(): string
    {
        return $this->isCustom() ? url('p/' . $this->slug) : url($this->path ?: '/');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    /** The body with scripts and event handlers removed (same rules as articles). */
    public function bodyHtml(): string
    {
        $t = strip_tags((string) $this->body, '<p><br><b><strong><i><em><u><s><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><blockquote><table><thead><tbody><tr><th><td><span><div><hr><pre><code><figure><figcaption><sub><sup><iframe>');
        $t = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $t);
        $t = preg_replace('/(href|src)\s*=\s*(["\'])\s*(javascript|data):[^"\']*\2/i', '$1=$2#$2', $t);
        // an embedded video may only come from YouTube
        $t = preg_replace_callback('/<iframe[^>]*>(\s*<\/iframe>)?/i', fn ($m) => preg_match('/src\s*=\s*["\']https:\/\/(www\.)?youtube(-nocookie)?\.com\//i', $m[0]) ? $m[0] : '', $t);

        return $t;
    }
}
