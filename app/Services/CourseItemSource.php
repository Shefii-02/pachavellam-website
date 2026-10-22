<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/** Reads the banks (daily exams, OMR exams, class categories ...) a course can take items from. See config/course_item_sources.php. */
class CourseItemSource
{
    /** The source for a feature on an exam type, or null when no bank is connected there. */
    public static function config(string $featureSlug, string $examSlug): ?array
    {
        $cfg = config('course_item_sources.' . $featureSlug);

        return $cfg && in_array($examSlug, $cfg['exams'], true) ? $cfg : null;
    }

    private static function base(array $cfg)
    {
        $q = DB::table($cfg['table']);
        foreach ($cfg['where'] as $col => $val) {
            $q->where($cfg['table'] . '.' . $col, $val);
        }

        return $q->selectRaw("{$cfg['table']}.id as source_id, ({$cfg['title']}) as title, ({$cfg['subtitle']}) as subtitle, ({$cfg['published']}) as published");
    }

    /** id => row, for ids that still exist in the bank. */
    public static function find(array $cfg, array $ids)
    {
        if (!$ids) {
            return collect();
        }

        return self::base($cfg)->whereIn($cfg['table'] . '.id', $ids)->get()->keyBy('source_id');
    }

    /** Which of these ids really are items of the bank (so a made-up id can never be attached). */
    public static function existing(array $cfg, array $ids): array
    {
        return self::find($cfg, array_map('intval', $ids))->keys()->map(fn ($k) => (int) $k)->all();
    }

    /** Items not attached yet, newest first, with a search box. */
    public static function candidates(array $cfg, array $exclude, ?string $term, int $page, int $perPage = 15, ?string $path = null, array $query = []): LengthAwarePaginator
    {
        $q = self::base($cfg);

        if ($exclude) {
            $q->whereNotIn($cfg['table'] . '.id', $exclude);
        }
        if ($term !== null && $term !== '') {
            $q->where(function ($w) use ($cfg, $term) {
                foreach ($cfg['search'] as $col) {
                    $w->orWhere($cfg['table'] . '.' . $col, 'like', '%' . $term . '%');
                }
            });
        }

        $total = (clone $q)->count();
        $rows = $q->orderByRaw($cfg['order'])->forPage($page, $perPage)->get();

        return new LengthAwarePaginator($rows, $total, $perPage, $page, ['path' => $path ?? request()->url(), 'query' => $query, 'pageName' => 'page']);
    }
}
