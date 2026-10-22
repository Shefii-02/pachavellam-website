<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Lists the folders and class files of a storage disk (S3 bucket, local disk ...), so classes can be picked like a library.
 * Everything is limited to the configured root folder; paths with ".." are refused.
 */
class StorageBrowser
{
    private string $disk;
    private string $root;

    public function __construct(?string $disk = null)
    {
        $this->disk = $disk ?: (string) config('class_library.storage.disk');
        $this->root = trim((string) config('class_library.storage.root'), '/');
    }

    public function configured(): bool
    {
        return $this->disk !== '' && config("filesystems.disks.{$this->disk}") !== null;
    }

    public function disk(): string
    {
        return $this->disk;
    }

    /** Folder path used inside the browser (no root prefix, no leading slash). */
    public function clean(?string $path): string
    {
        $path = trim(str_replace('\\', '/', (string) $path), '/');
        abort_if(str_contains($path, '..'), 422, 'Invalid folder.');

        return $path;
    }

    /** The full path on the disk for a browser path. */
    public function full(string $path): string
    {
        return trim($this->root . '/' . $this->clean($path), '/');
    }

    /**
     * @return array{dirs: array, files: array, truncated: bool}
     * @throws \Throwable when the storage cannot be reached
     */
    public function browse(?string $path): array
    {
        $path = $this->clean($path);
        $fs = Storage::disk($this->disk);
        $full = $this->full($path);
        $max = (int) config('class_library.storage.max_listing', 400);
        $extensions = (array) config('class_library.storage.extensions');
        $strip = fn (string $p) => $this->root === '' ? $p : ltrim(substr($p, strlen($this->root)), '/');

        $dirs = collect($fs->directories($full))->map(fn ($d) => ['name' => basename($d), 'path' => $strip($d)])->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();

        $files = [];
        $truncated = false;
        foreach ($fs->files($full) as $file) {
            if (!in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $extensions, true)) {
                continue;
            }
            if (count($files) >= $max) {
                $truncated = true;
                break;
            }
            $files[] = ['name' => basename($file), 'path' => $strip($file), 'full' => $file, 'ext' => strtolower(pathinfo($file, PATHINFO_EXTENSION))];
        }
        usort($files, fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        return ['dirs' => $dirs, 'files' => $files, 'truncated' => $truncated];
    }

    /** "03_polity-basics_part-1.mp4" -> "Polity basics part 1" */
    public static function titleFromFile(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = preg_replace('/^\d+[\s._-]+/', '', $base);
        $base = trim(preg_replace('/[\s_-]+/', ' ', $base));

        return $base === '' ? pathinfo($name, PATHINFO_FILENAME) : mb_strtoupper(mb_substr($base, 0, 1)) . mb_substr($base, 1);
    }
}
