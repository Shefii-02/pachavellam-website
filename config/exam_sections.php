<?php

/*
 | Content sections available inside every exam module (RRB, SSC, UGC NET, KTET).
 |
 | Each key is the url segment (admin/<exam>/<key>); the table is "<prefix>_<key with - as _>",
 | e.g. rrb_study_materials. Every table also gets: id, position, status, created_by, timestamps.
 |
 | Field types: text, url, textarea, number, date, select, image, file, category, checkbox
 | `list` = columns shown on the index screen.
 */

return [

    'banners' => [
        'label' => 'Banners',
        'icon' => 'bi-card-image',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'image', 'label' => 'Banner Image', 'type' => 'image', 'required' => true],
            ['name' => 'link_url', 'label' => 'Redirect Link', 'type' => 'url'],
            ['name' => 'starts_at', 'label' => 'Show From', 'type' => 'date'],
            ['name' => 'ends_at', 'label' => 'Show Until', 'type' => 'date'],
        ],
        'list' => ['image', 'title', 'link_url', 'ends_at'],
    ],

    'whats-new' => [
        'label' => "What's New",
        'icon' => 'bi-plus-circle',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'link_url', 'label' => 'Link', 'type' => 'url'],
            ['name' => 'image', 'label' => 'Image', 'type' => 'image'],
        ],
        'list' => ['image', 'title', 'link_url'],
    ],

    'announcements' => [
        'label' => 'Announcements',
        'icon' => 'bi-megaphone',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'type', 'label' => 'Type', 'type' => 'select', 'required' => true,
                'options' => ['exam' => 'Exam', 'other' => 'Other']],
            ['name' => 'description', 'label' => 'Details', 'type' => 'textarea'],
            ['name' => 'published_at', 'label' => 'Publish Date', 'type' => 'date'],
            ['name' => 'link_url', 'label' => 'Link', 'type' => 'url'],
            ['name' => 'attachment', 'label' => 'Attachment', 'type' => 'file'],
            ['name' => 'is_pinned', 'label' => 'Pin to top', 'type' => 'checkbox'],
        ],
        'list' => ['title', 'type', 'published_at', 'is_pinned'],
    ],

    'study-materials' => [
        'label' => 'Study Materials',
        'icon' => 'bi-file-earmark-arrow-down-fill',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'category_id', 'label' => 'Category', 'type' => 'category'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'file', 'label' => 'File (PDF / document)', 'type' => 'file'],
            ['name' => 'link_url', 'label' => 'External Link', 'type' => 'url'],
            ['name' => 'cover', 'label' => 'Cover Image', 'type' => 'image'],
        ],
        'list' => ['cover', 'title', 'category_id', 'file'],
    ],

    'previous-questions' => [
        'label' => 'PYQ',
        'icon' => 'bi-journal-text',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'category_id', 'label' => 'Category', 'type' => 'category'],
            ['name' => 'year', 'label' => 'Year', 'type' => 'number'],
            ['name' => 'file', 'label' => 'Question Paper (PDF)', 'type' => 'file'],
            ['name' => 'link_url', 'label' => 'External Link', 'type' => 'url'],
        ],
        'list' => ['title', 'category_id', 'year', 'file'],
    ],

    'notifications' => [
        'label' => 'Notifications',
        'icon' => 'bi-bell',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'category_id', 'label' => 'Category', 'type' => 'category'],
            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
            ['name' => 'published_at', 'label' => 'Notification Date', 'type' => 'date'],
            ['name' => 'last_date', 'label' => 'Last Date to Apply', 'type' => 'date'],
            ['name' => 'link_url', 'label' => 'Apply / Official Link', 'type' => 'url'],
            ['name' => 'attachment', 'label' => 'Notification File', 'type' => 'file'],
        ],
        'list' => ['title', 'category_id', 'published_at', 'last_date'],
    ],

    'syllabus' => [
        'label' => 'Syllabus',
        'icon' => 'bi-list-check',
        'fields' => [
            ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'required' => true],
            ['name' => 'category_id', 'label' => 'Category', 'type' => 'category'],
            ['name' => 'description', 'label' => 'Syllabus Details', 'type' => 'textarea'],
            ['name' => 'file', 'label' => 'Syllabus File (PDF)', 'type' => 'file'],
        ],
        'list' => ['title', 'category_id', 'file'],
    ],

    'stories' => [
        'label' => 'Stories (Achievements)',
        'icon' => 'bi-trophy',
        'fields' => [
            ['name' => 'name', 'label' => 'Achiever Name', 'type' => 'text', 'required' => true],
            ['name' => 'title', 'label' => 'Headline', 'type' => 'text', 'required' => true],
            ['name' => 'rank', 'label' => 'Rank / Post', 'type' => 'text'],
            ['name' => 'exam_year', 'label' => 'Year', 'type' => 'number'],
            ['name' => 'description', 'label' => 'Story', 'type' => 'textarea'],
            ['name' => 'photo', 'label' => 'Photo', 'type' => 'image'],
        ],
        'list' => ['photo', 'name', 'title', 'rank', 'exam_year'],
    ],

];
