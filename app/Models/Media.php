<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/** One file of the media library (picture, video, voice, document ...). `path` is relative to storage/app/public/files/. */
class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';
    protected $guarded = ['id'];

    public function folder()
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /** `path` is relative to the disk root (files/content/a.png, news/b.jpg ...). */
    public function url(): string
    {
        return Storage::url($this->path);
    }

    /**
     * What a content field stores for this file: the path below files/ (content items read Storage::url('files/' . value)).
     * Files kept elsewhere on the disk (old modules) have none - they can still be used through the upload-from-library copy.
     */
    public function fieldValue(): ?string
    {
        return str_starts_with($this->path, 'files/') ? substr($this->path, 6) : null;
    }

    public function sizeLabel(): string
    {
        $b = (int) $this->size;

        return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
    }

    /** What the picker and the library page need to draw one tile. */
    public function toTile(): array
    {
        return [
            'id' => $this->id, 'path' => $this->fieldValue(), 'disk_path' => $this->path, 'url' => $this->url(), 'name' => $this->title ?: $this->name, 'file' => $this->name, 'type' => $this->type, 'ext' => $this->ext,
            'size' => $this->sizeLabel(), 'bytes' => (int) $this->size, 'w' => $this->width, 'h' => $this->height, 'alt' => $this->alt, 'folder_id' => $this->folder_id,
            'date' => optional($this->created_at)->format('d M Y'),
        ];
    }
}
