<?php

namespace App\Support;

use App\Models\CompetitiveExam;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Title, description, keywords, share image and robots of every public page.
 * Staff set them in admin/seo (table seo_pages); anything left empty falls back to the text the page itself supplies
 * (Seo::set(...) in the controller) and then to the site default.
 */
class Seo
{
    const SITE = 'Pachavellam Education';
    const DEFAULT_DESCRIPTION = 'Free classes, daily exams, mock tests, previous question papers and notes for Kerala PSC, KTET, RRB, SSC, UGC NET and other competitive exams.';
    const DEFAULT_KEYWORDS = 'Pachavellam, PSC free class, Kerala PSC, daily exam, mock test, PSC coaching, competitive exams';

    /** page key => [label, path shown to staff, automatic title, automatic description] - the fixed pages. Exam types are added from the database. */
    public static function pages(): array
    {
        $p = [
            'home' => ['Home page', '/', 'Pachavellam Education - Free PSC classes, daily exams & mock tests', self::DEFAULT_DESCRIPTION],
            'exams' => ['Exams', '/exams', 'Exams - choose your exam | Pachavellam', 'Pick the competitive exam you are preparing for and get its classes, tests and notes.'],
            'courses' => ['Courses', '/courses', 'Courses - live & recorded classes | Pachavellam', 'Live and recorded courses with mock tests, notes and doubt support for competitive exams.'],
            'learn' => ['Learn', '/learn', 'Learn - classes, tests & articles | Pachavellam', 'Your classes, upcoming tests and free articles in one place.'],
            'free-classes' => ['Free classes', '/free-classes', 'Free classes - Pachavellam', 'Watch free live and recorded classes for competitive exams.'],
            'articles' => ['Articles', '/articles', 'Free articles - Pachavellam', 'Free exam articles, notes and study tips.'],
            'announcements' => ['Announcements', '/announcements', 'Announcements - Pachavellam', 'Latest exam notifications and announcements.'],
            'stories' => ['Stories & achievements', '/stories', 'Stories & achievements - Pachavellam', 'Success stories and achievements of Pachavellam students.'],
            'sign-in' => ['Sign in', '/sign-in', 'Sign in - Pachavellam', 'Sign in with your mobile number or Google to join classes and take tests.'],
        ];
        if (Schema::hasTable('competitive_exams')) {
            foreach (CompetitiveExam::where('status', 1)->orderBy('position')->get(['name', 'short_name', 'slug']) as $e) {
                $n = $e->short_name ?: $e->name;
                $p['exam:' . $e->slug] = [$n . ' (exam type page)', '/' . $e->slug, $n . ' - free classes, daily exams & mock tests | Pachavellam',
                    'Free classes, daily exams, mock tests, previous question papers and notes for ' . $e->name . '.'];
            }
        }

        return $p;
    }

    /** The page's own text, set by its controller: Seo::set(['key' => 'exam:kpsc', 'title' => ..., 'description' => ..., 'image' => url]) */
    public static function set(array $data): void
    {
        app()->instance('seo.page', $data);
    }

    private static function overrides(string $key): ?object
    {
        if (!Schema::hasTable('seo_pages')) {
            return null;
        }

        return Cache::remember('seo_page_' . $key, 600, fn () => DB::table('seo_pages')->where('page_key', $key)->first() ?: false) ?: null;
    }

    /** Fixed pages found from the address, when the controller did not name one. */
    private static function keyFromPath(): ?string
    {
        $path = trim(request()->path(), '/');
        $map = ['' => 'home', 'exams' => 'exams', 'courses' => 'courses', 'learn' => 'learn', 'free-classes' => 'free-classes', 'articles' => 'articles', 'announcements' => 'announcements', 'stories' => 'stories', 'sign-in' => 'sign-in'];

        return $map[$path] ?? null;
    }

    /** @return array{title:string,description:string,keywords:string,image:string,robots:string,canonical:string,key:?string} */
    public static function current(string $sectionTitle = ''): array
    {
        $own = app()->bound('seo.page') ? app('seo.page') : [];
        $key = $own['key'] ?? self::keyFromPath();
        $fixed = $key ? (self::pages()[$key] ?? null) : null;
        $row = $key ? self::overrides($key) : null;

        $autoTitle = $own['title'] ?? ($fixed[2] ?? ($sectionTitle !== '' ? self::SITE . ' :: ' . $sectionTitle : self::SITE));
        $autoDesc = $own['description'] ?? ($fixed[3] ?? self::DEFAULT_DESCRIPTION);
        $image = !empty($row->og_image) ? Storage::url('files/' . $row->og_image) : ($own['image'] ?? url('assets/images/favicon.png'));

        return [
            'key' => $key,
            'title' => trim((string) ($row->title ?? '')) ?: $autoTitle,
            'description' => Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) (($row->description ?? '') ?: $autoDesc)))), 300, ''),
            'keywords' => trim((string) ($row->keywords ?? '')) ?: ($own['keywords'] ?? self::DEFAULT_KEYWORDS),
            'image' => str_starts_with($image, 'http') ? $image : url($image),
            'robots' => trim((string) ($row->robots ?? '')) ?: ($own['robots'] ?? 'index,follow'),
            'canonical' => $own['canonical'] ?? url(request()->path() === '/' ? '/' : request()->path()),
        ];
    }

    public static function forget(string $key): void
    {
        Cache::forget('seo_page_' . $key);
    }
}
