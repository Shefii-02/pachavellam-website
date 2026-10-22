<?php

namespace App\Services;

use App\Models\ContentItem;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Imports the stories of an Instagram highlight into Achievements.
 * Staff open the highlight in a downloader in their own browser and paste what it returns (JSON);
 * the pictures and videos are copied onto our own storage straight away, because Instagram's links expire within a day.
 *
 * A highlight can hold ~100 videos, more than one web request may take. So each run works for a limited time (default 20 s,
 * four stories at a time) and reports how many are left; running it again with the same paste carries on where it stopped.
 */
class AchievementImporter
{
    private const HOSTS = '/(^|\.)(cdninstagram\.com|fbcdn\.net)$/i';
    private const MAX_BYTES = 80 * 1024 * 1024;
    private const AT_A_TIME = 4;

    /** @return array{added:int, skipped:int, repaired:int, failed:int, remaining:int, errors:array<int,string>} */
    public function import(string $json, ?string $highlightUrl, int $userId, array $examIds = [], int $seconds = 20): array
    {
        $data = json_decode($json, true);
        $items = $data['result']['insBos'] ?? $data['insBos'] ?? null;
        if (!is_array($items)) {
            throw new \InvalidArgumentException('That is not the JSON of a highlight. Paste the whole response that the downloader gave.');
        }
        @set_time_limit($seconds + 40);
        $deadline = microtime(true) + $seconds;

        $out = ['added' => 0, 'skipped' => 0, 'repaired' => 0, 'failed' => 0, 'remaining' => 0, 'errors' => []];
        $disk = Storage::disk('public');

        // 1. sort what is already imported (and bring back files that went missing) from what is new
        $new = [];
        $broken = [];
        foreach (array_reverse($items) as $it) {   // oldest first, so the newest ends up on top
            $ref = (string) ($it['id'] ?? '');
            if ($ref === '') {
                $out['skipped']++;
                continue;
            }
            $existing = ContentItem::withTrashed()->where('kind', 'achievement')->where('source_ref', $ref)->first();
            if (!$existing) {
                $new[] = $it;
                continue;
            }
            $missing = ($existing->image && !$disk->exists('files/' . $existing->image)) || ($existing->media_path && !$disk->exists('files/' . $existing->media_path));
            if ($missing && !$existing->trashed()) {
                $broken[] = [$existing, $it];
            } else {
                $out['skipped']++;
            }
        }

        // 2. download in small groups until the time is up
        $work = array_merge(array_map(fn ($b) => ['row' => $b[0], 'it' => $b[1]], $broken), array_map(fn ($it) => ['row' => null, 'it' => $it], $new));
        $position = (int) ContentItem::ofKind('achievement')->max('position');
        foreach (array_chunk($work, self::AT_A_TIME) as $n => $group) {
            if ($n > 0 && microtime(true) > $deadline) {
                $out['remaining'] += count($group);
                continue;
            }
            $files = $this->fetchGroup($group);
            foreach ($group as $k => $job) {
                $it = $job['it'];
                $ref = (string) $it['id'];
                $isVideo = ($it['type'] ?? '') === 'mp4';
                [$media, $cover] = $files[$k];
                $cover = $isVideo ? $cover : $media;
                if (!$media || !$cover) {
                    $out['failed']++;
                    $out['errors'][] = $ref . ': the file could not be downloaded (the link may have expired - open the highlight again and paste a fresh response)';
                    continue;
                }
                if ($job['row']) {
                    $job['row']->update(['image' => $cover, 'media_path' => $isVideo ? $media : null]);
                    $out['repaired']++;
                    continue;
                }
                $row = ContentItem::create([
                    'kind' => 'achievement', 'title' => 'Achievement', 'status' => true, 'position' => ++$position, 'created_by' => $userId,
                    'source_ref' => $ref, 'media_type' => $isVideo ? 'video' : 'image', 'duration' => $it['duration'] ?? null,
                    'image' => $cover, 'media_path' => $isVideo ? $media : null, 'link_url' => $highlightUrl ?: null, 'published_at' => today(),
                ]);
                if ($examIds) {
                    $row->exams()->sync($examIds);
                }
                $out['added']++;
            }
        }

        return $out;
    }

    /**
     * Downloads the video/picture and the cover of a few stories at the same time.
     * @return array<int, array{0:?string,1:?string}> per story: [media path, cover path]
     */
    private function fetchGroup(array $group): array
    {
        $want = [];
        foreach ($group as $k => $job) {
            $it = $job['it'];
            $isVideo = ($it['type'] ?? '') === 'mp4';
            $want["m$k"] = [$it['originalUrl'] ?? '', $isVideo ? 'mp4' : 'jpg'];
            if ($isVideo) {
                $want["c$k"] = [$it['originalCover'] ?? '', 'jpg'];
            }
        }
        $valid = array_filter($want, fn ($w) => $w[0] && preg_match(self::HOSTS, (string) parse_url($w[0], PHP_URL_HOST)));   // only Instagram's own CDN is ever fetched

        $responses = $valid ? Http::pool(function (Pool $pool) use ($valid) {
            foreach ($valid as $key => [$url]) {
                $pool->as($key)->timeout(60)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            }
        }) : [];

        $saved = [];
        foreach ($valid as $key => [$url, $ext]) {
            $res = $responses[$key] ?? null;
            if ($res instanceof \Illuminate\Http\Client\Response && $res->ok() && strlen($res->body()) >= 500 && strlen($res->body()) <= self::MAX_BYTES) {
                $name = 'achievements/' . Str::random(40) . '.' . $ext;
                Storage::disk('public')->put('files/' . $name, $res->body());
                $saved[$key] = $name;
            }
        }

        return array_map(fn ($k) => [$saved["m$k"] ?? null, $saved["c$k"] ?? null], array_keys($group));
    }
}
