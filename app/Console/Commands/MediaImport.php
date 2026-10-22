<?php

namespace App\Console\Commands;

use App\Models\MediaFolder;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Index the files already on the disk (storage/app/public/**) in the media library, folder by folder.
 * Nothing is moved or renamed, so every existing link keeps working. Safe to run again: files already indexed are skipped.
 */
class MediaImport extends Command
{
    protected $signature = 'media:import {--dry : only count}';
    protected $description = 'Add the files already uploaded (all modules) to the media library, in folders named after where they are kept';

    private array $folders = [];

    private function folderFor(array $segments): ?int
    {
        $parent = null;
        $key = '';
        foreach ($segments as $seg) {
            $key .= '/' . $seg;
            if (!isset($this->folders[$key])) {
                $name = $key === '/' . $segments[0] ? $this->pretty($seg) : $seg;
                $f = MediaFolder::firstOrCreate(['parent_id' => $parent, 'name' => $name]);
                $this->folders[$key] = $f->id;
            }
            $parent = $this->folders[$key];
        }

        return $parent;
    }

    private function pretty(string $dir): string
    {
        return ['files' => 'Website content (files)', 'filess' => 'Old uploads (filess)', 'users' => 'Student photos', 'profile_images' => 'Profile photos'][$dir]
            ?? Str::title(str_replace(['-', '_'], ' ', $dir));
    }

    public function handle(MediaService $media): int
    {
        $disk = Storage::disk(config('media.disk'));
        // student photos are personal and not content: they stay out of the library
        $files = collect($disk->allFiles())->reject(fn ($f) => str_contains($f, '/.') || str_starts_with(basename($f), '.') || str_starts_with($f, 'files/media/') || preg_match('#^(users|profile_images)/#', $f))
            ->filter(fn ($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), MediaService::allowedExtensions(), true))->values();
        $this->info($files->count() . ' files found.');
        if ($this->option('dry')) {
            $files->groupBy(fn ($f) => explode('/', $f)[0])->each(fn ($g, $k) => $this->line(str_pad($k, 22) . $g->count()));

            return 0;
        }
        $bar = $this->output->createProgressBar($files->count());
        $new = 0;
        foreach ($files as $f) {
            $segments = array_slice(explode('/', $f), 0, -1);
            $folderId = $segments ? $this->folderFor($segments) : $this->folderFor(['Other files']);
            if ($media->indexExisting($f, $folderId)) {
                $new++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("$new new files added to the media library.");

        return 0;
    }
}
