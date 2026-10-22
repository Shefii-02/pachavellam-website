<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Services\ClassSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/** One recorded class: a link to a video (YouTube / CDN / cloud storage) with its details and watch rules. */
class LibraryClass extends Model
{
    use LogsActivity;
    use SoftDeletes;
    protected bool $tracksDeleter = true;
    protected $table = 'library_classes';

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array',
        'class_date' => 'date',
        'allow_speed' => 'boolean',
        'allow_seek' => 'boolean',
        'allow_download' => 'boolean',
        'resume_playback' => 'boolean',
        'watermark_user' => 'boolean',
    ];

    public function folder()
    {
        return $this->belongsTo(ClassFolder::class, 'folder_id');
    }

    public function courseLinks()
    {
        return $this->hasMany(CourseClass::class, 'class_id');
    }

    public function watchLogs()
    {
        return $this->hasMany(ClassWatchLog::class, 'class_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function sourceLabel(): string
    {
        return config('class_library.sources.' . $this->source, $this->source);
    }

    public function durationLabel(): string
    {
        return ClassSource::formatDuration($this->duration_seconds);
    }

    /** The address the player opens: the link itself, or a (temporary) address of the file on the storage disk. */
    public function playUrl(): ?string
    {
        if ($this->source === 's3' || ($this->storage_disk && $this->storage_path)) {
            try {
                $disk = Storage::disk($this->storage_disk);
                $minutes = (int) config('class_library.storage.link_minutes', 360);

                return $disk->providesTemporaryUrls()
                    ? $disk->temporaryUrl($this->storage_path, now()->addMinutes($minutes))
                    : $disk->url($this->storage_path);
            } catch (\Throwable $e) {
                return $this->url;   // storage not reachable: fall back to a saved address if there is one
            }
        }

        return $this->url;
    }

    /** YouTube embed address for the web player (null for other sources). */
    public function embedUrl(): ?string
    {
        $id = $this->source === 'youtube' ? ClassSource::youtubeId((string) $this->url) : null;

        return $id ? 'https://www.youtube-nocookie.com/embed/' . $id : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail) {
            return preg_match('#^https?://#', $this->thumbnail) ? $this->thumbnail : asset('storage/' . $this->thumbnail);
        }
        $id = $this->source === 'youtube' ? ClassSource::youtubeId((string) $this->url) : null;

        return $id ? "https://img.youtube.com/vi/{$id}/mqdefault.jpg" : null;
    }

    /** One line describing the watch rules, for lists. */
    public function rulesSummary(): string
    {
        $parts = [];
        if ($this->max_watch_minutes) {
            $parts[] = $this->max_watch_minutes . ' min per user';
        }
        if ($this->max_views) {
            $parts[] = $this->max_views . ' view(s) per user';
        }
        if ($this->access_days) {
            $parts[] = $this->access_days . ' days access';
        }
        if (!$this->allow_seek) {
            $parts[] = 'no skipping';
        }
        if (!$this->allow_speed) {
            $parts[] = 'fixed speed';
        }
        if ($this->watermark_user) {
            $parts[] = 'watermark';
        }

        return $parts ? implode(' · ', $parts) : 'no limits';
    }
}
