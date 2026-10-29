<?php

namespace App\Support;

use App\Models\ExamCategory;

/**
 * What a category / sub category page shows, set per category in the panel (Exam categories > edit > Public page):
 * which sections (courses, prime tests, free tests, free classes, articles, sub categories), in which order, as tabs or stacked,
 * and whether item taps open in a new tab / a copy-link button shows.
 * A category without its own setting uses its parent's, then the defaults.
 */
class CategorySections
{
    public const ALL = [
        'sub_categories' => 'Sub categories',
        'courses' => 'Courses',
        'prime_tests' => 'Prime tests (paid)',
        'free_tests' => 'Free tests',
        'free_classes' => 'Free classes',
        'articles' => 'Articles',
    ];

    public const DEFAULTS = ['sections' => ['sub_categories', 'courses', 'prime_tests', 'free_tests', 'free_classes', 'articles'], 'layout' => 'tabs', 'new_tab' => true, 'copy_link' => true, 'own' => false];

    /** @return array{sections: string[], layout: string, new_tab: bool, copy_link: bool, own: bool} */
    public static function for(ExamCategory $cat): array
    {
        $node = $cat;
        for ($i = 0; $node && $i < 10; $i++) {
            $s = (array) $node->settings;
            if (isset($s['page_sections']) && is_array($s['page_sections'])) {
                return [
                    'sections' => array_values(array_filter($s['page_sections'], fn ($k) => isset(self::ALL[$k]))),
                    'layout' => ($s['page_layout'] ?? 'tabs') === 'stacked' ? 'stacked' : 'tabs',
                    'new_tab' => (bool) ($s['page_new_tab'] ?? true), 'copy_link' => (bool) ($s['page_copy_link'] ?? true), 'own' => $node->id === $cat->id,
                ];
            }
            $node = $node->parent_id ? ExamCategory::find($node->parent_id) : null;
        }

        return self::DEFAULTS;
    }

    /** Form fields -> settings keys (merged into the category's other settings). Null = "use the parent's / default" (the keys are removed). */
    public static function fromRequest($request, ?array $existing): array
    {
        $settings = (array) $existing;
        unset($settings['page_sections'], $settings['page_layout'], $settings['page_new_tab'], $settings['page_copy_link']);
        if ($request->boolean('inherit_page')) {
            return $settings;
        }
        $rows = collect((array) $request->input('ps'))->filter(fn ($r, $k) => isset(self::ALL[$k]) && !empty($r['on']))->sortBy(fn ($r) => (int) ($r['order'] ?? 0))->keys()->values()->all();
        $settings['page_sections'] = $rows;
        $settings['page_layout'] = $request->input('page_layout') === 'stacked' ? 'stacked' : 'tabs';
        $settings['page_new_tab'] = $request->boolean('page_new_tab');
        $settings['page_copy_link'] = $request->boolean('page_copy_link');

        return $settings;
    }
}
