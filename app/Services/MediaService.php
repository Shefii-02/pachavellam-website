<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Puts uploaded files into the media library (storage/app/public/files/media/YYYY/MM/...) and describes them. */
class MediaService
{
    public static function typeOf(?string $ext): string
    {
        $ext = strtolower((string) $ext);
        foreach (config('media.types') as $type => $list) {
            if (in_array($ext, $list, true)) {
                return $type;
            }
        }

        return 'other';
    }

    public static function allowedExtensions(): array
    {
        return array_merge(...array_values(config('media.types')));
    }

    /** Store one upload. Throws InvalidArgumentException with a message the staff can read. */
    public function store(UploadedFile $file, ?int $folderId, ?int $userId): Media
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        if (!in_array($ext, self::allowedExtensions(), true)) {
            throw new \InvalidArgumentException('"' . $file->getClientOriginalName() . '": this file type (.' . ($ext ?: '?') . ') cannot be uploaded.');
        }
        $limit = (float) config('media.max_mb', 64) * 1048576;
        if ($file->getSize() > $limit) {
            throw new \InvalidArgumentException('"' . $file->getClientOriginalName() . '" is bigger than ' . config('media.max_mb') . ' MB.');
        }
        $type = self::typeOf($ext);
        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        // the contents must match the kind: a picture must be a picture
        if ($type === 'image' && !str_starts_with((string) $mime, 'image/')) {
            throw new \InvalidArgumentException('"' . $file->getClientOriginalName() . '" is not a real picture.');
        }
        [$w, $h] = [null, null];
        if ($type === 'image' && ($info = @getimagesize($file->getRealPath()))) {
            [$w, $h] = [$info[0], $info[1]];
        }

        $dir = trim(config('media.root'), '/') . '/' . now()->format('Y/m');
        $name = Str::random(40) . '.' . $ext;
        Storage::disk(config('media.disk'))->putFileAs($dir, $file, $name);
        $path = $dir . '/' . $name;   // relative to the disk root (files/media/2026/10/...)

        return Media::create([
            'folder_id' => $folderId, 'path' => $path, 'name' => Str::limit($file->getClientOriginalName(), 240, ''), 'type' => $type, 'ext' => $ext, 'mime' => $mime,
            'size' => $file->getSize(), 'width' => $w, 'height' => $h, 'uploaded_by' => $userId, 'source' => 'upload',
        ]);
    }

    /** Index a file that is already on the disk (kept exactly where it is, so every old link keeps working). */
    public function indexExisting(string $path, ?int $folderId): ?Media
    {
        $disk = Storage::disk(config('media.disk'));
        if (!$disk->exists($path) || Media::withTrashed()->where('path', $path)->exists()) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = self::typeOf($ext);
        [$w, $h] = [null, null];
        if ($type === 'image' && ($info = @getimagesize($disk->path($path)))) {
            [$w, $h] = [$info[0], $info[1]];
        }

        return Media::create([
            'folder_id' => $folderId, 'path' => $path, 'name' => basename($path), 'type' => $type, 'ext' => $ext, 'mime' => null,
            'size' => (int) $disk->size($path), 'width' => $w, 'height' => $h, 'source' => 'import',
            'created_at' => \Illuminate\Support\Carbon::createFromTimestamp($disk->lastModified($path)), 'updated_at' => now(),
        ]);
    }

    public function folderPath(?int $id): array
    {
        return $id && ($f = MediaFolder::find($id)) ? array_map(fn ($x) => ['id' => $x->id, 'name' => $x->name], $f->trail()) : [];
    }
}
