<?php

namespace App\Support;

/** The pages / features where an ad can be placed (checkboxes in admin/ads). The same keys are used by the website and the apps. */
class AdPages
{
    public const ALL = [
        'home' => 'Home',
        'exam' => 'Exam type page',
        'courses' => 'Course list',
        'course_detail' => 'Course details',
        'classes' => 'Free classes list',
        'class_player' => 'Free class player',
        'articles' => 'Free articles list',
        'article_detail' => 'Article / announcement page',
        'category' => 'Category page',
        'my_courses' => 'My courses (bought)',
    ];

    /** Pages that are long paged lists (in-list ads and "every N items" make sense there). */
    public const LISTS = ['courses', 'classes', 'articles', 'category'];
}
