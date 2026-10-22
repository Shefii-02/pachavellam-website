<?php

namespace App\Support;

use App\Models\CompetitiveExam;
use App\Models\ExamCourse;

/**
 * Where a tap on a banner / story / push notification lands.
 *   link    a custom web link
 *   course  one course            (web: /courses/{slug}      app: pachavellam://course/{id})
 *   exam    one exam type         (web: /exam/{slug}         app: pachavellam://exam/{slug})
 *   page    a named page          (see PAGES)
 *   none    nothing happens
 * The same value is sent to the apps as data (landing_type / landing_value / landing_url) so Android, iOS and web open the same place.
 */
class Landing
{
    public const TYPES = ['link' => 'Custom link', 'course' => 'A course', 'exam' => 'An exam type', 'page' => 'A page', 'none' => 'Nothing'];

    /** key => [label, web path] */
    public const PAGES = [
        'home' => ['Home', '/'],
        'kpsc' => ['KPSC home', '/kpsc'],
        'daily-exam' => ['Daily exams', '/kpsc/daily-exam'],
        'free-classes' => ['Free classes', '/free-classes'],
        'articles' => ['Free articles', '/articles'],
        'contact-us' => ['Contact us', '/contact-us'],
        'about-us' => ['About us', '/about-us'],
        'faq' => ['FAQ', '/faq'],
    ];

    public static function resolve(?string $type, ?string $value, ?string $link = null): array
    {
        $type = $type ?: 'link';
        $url = null;
        $app = null;

        switch ($type) {
            case 'course':
                $c = $value ? ExamCourse::find($value) : null;
                if ($c) {
                    $url = url('courses/' . ($c->slug ?: $c->id));
                    $app = 'pachavellam://course/' . $c->id;
                }
                break;
            case 'exam':
                if ($value) {
                    $url = url($value);
                    $app = 'pachavellam://exam/' . $value;
                }
                break;
            case 'page':
                if ($value && isset(self::PAGES[$value])) {
                    $url = url(self::PAGES[$value][1]);
                    $app = 'pachavellam://page/' . $value;
                }
                break;
            case 'none':
                break;
            default:
                $url = $link ?: null;
                $app = $url;
        }

        return ['type' => $type, 'value' => $value, 'url' => $url, 'app' => $app];
    }

    /** Web url for a tap (or null for nothing). */
    public static function url(?string $type, ?string $value, ?string $link = null): ?string
    {
        return self::resolve($type, $value, $link)['url'];
    }

    /** Options for the form pickers. */
    public static function options(): array
    {
        return [
            'courses' => ExamCourse::where('status', 'published')->orderBy('title')->get(['id', 'title']),
            'exams' => CompetitiveExam::orderBy('position')->get(['id', 'slug', 'short_name', 'name']),
            'pages' => collect(self::PAGES)->map(fn ($p) => $p[0])->all(),
        ];
    }

    /** Validated request fields -> [target_type, target_value]. */
    public static function fromRequest($request): array
    {
        $type = in_array($request->input('target_type'), array_keys(self::TYPES), true) ? $request->input('target_type') : 'link';
        $value = match ($type) {
            'course' => (string) (int) $request->input('target_course'),
            'exam' => (string) $request->input('target_exam'),
            'page' => (string) $request->input('target_page'),
            default => null,
        };

        return [$type, $value !== '' && $value !== '0' ? $value : null];
    }
}
