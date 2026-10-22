<?php

namespace App\Services;

/**
 * Works out what kind of link a class address is, and turns it into something a player can open.
 *
 *   youtube : youtube.com/watch?v=ID, youtu.be/ID, /embed/ID, /live/ID, /shorts/ID   (unlisted videos work the same)
 *   cdn     : any http(s) address of a video / audio file or stream (.mp4 .m3u8 .mpd ...) or a host listed in config
 *   link    : any other address (opens as it is)
 */
class ClassSource
{
    public const VIDEO_EXTENSIONS = ['mp4', 'm4v', 'mov', 'webm', 'mkv', 'm3u8', 'mpd', 'mp3', 'm4a', 'aac', 'ogg'];

    /** @return array{source: string, url: string, video_id: ?string} */
    public static function detect(string $url): array
    {
        $url = trim($url);

        if ($id = self::youtubeId($url)) {
            return ['source' => 'youtube', 'url' => 'https://www.youtube.com/watch?v=' . $id, 'video_id' => $id];
        }

        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $cdnHosts = array_filter((array) config('class_library.cdn_hosts', []));

        $isCdn = in_array($ext, self::VIDEO_EXTENSIONS, true)
            || preg_match('/(^|\.)(cloudfront\.net|amazonaws\.com|b-cdn\.net|r2\.dev|digitaloceanspaces\.com|storage\.googleapis\.com|blob\.core\.windows\.net)$/', $host)
            || in_array($host, $cdnHosts, true);

        return ['source' => $isCdn ? 'cdn' : 'link', 'url' => $url, 'video_id' => null];
    }

    public static function youtubeId(string $url): ?string
    {
        if (!preg_match('#^https?://#i', $url)) {
            return null;
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^(www\.|m\.|music\.)/', '', $host);

        if ($host === 'youtu.be') {
            $id = trim((string) parse_url($url, PHP_URL_PATH), '/');

            return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
        }
        if (in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
            if (!empty($q['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $q['v'])) {
                return $q['v'];
            }
            if (preg_match('#^/(?:embed|live|shorts|v)/([A-Za-z0-9_-]{11})#', (string) parse_url($url, PHP_URL_PATH), $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /** "1:23:45", "12:30" or "90" (minutes: no) -> seconds. Returns null for empty / unreadable. */
    public static function parseDuration(?string $text): ?int
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }
        if (preg_match('/^(\d+):(\d{1,2})(?::(\d{1,2}))?$/', $text, $m)) {
            return isset($m[3]) && $m[3] !== '' ? ((int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3]) : ((int) $m[1] * 60 + (int) $m[2]);
        }
        if (preg_match('/^\d+$/', $text)) {
            return (int) $text * 60;   // a bare number is minutes
        }

        return null;
    }

    public static function formatDuration(?int $seconds): string
    {
        if (!$seconds) {
            return '—';
        }
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }
}
