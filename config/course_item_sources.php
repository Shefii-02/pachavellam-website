<?php

/*
 | Where a course feature takes its items from.
 |
 | Key = the feature slug. A feature that is not listed here (or is listed for other exam types only) has no bank connected
 | for that exam type yet: the course page says so and nothing can be attached.
 |
 |   exams      exam type slugs the source belongs to (these tables hold KPSC data)
 |   label      what one item is called
 |   table      source table
 |   where      fixed conditions [column => value]
 |   title      SQL expression for the item name           (trusted - written here, never from a request)
 |   subtitle   SQL expression for the small line under it
 |   search     columns the search box looks in
 |   order      SQL order
 |   published  SQL condition meaning "visible to students in the bank"
 |
 | To connect another exam type or feature, add an entry here. Nothing else changes.
 */

return [

    'daily-exams' => [
        'exams' => ['kpsc'],
        'label' => 'Daily exam',
        'table' => 'kpsc_daily_exams',
        'where' => ['section' => 'Daily Exam'],
        'title' => 'examtitle',
        'subtitle' => "CONCAT_WS(' · ', DATE_FORMAT(exam_date, '%d %b %Y'), (SELECT s.subject_title FROM kpsc_subjects s WHERE s.id = CAST(kpsc_daily_exams.subject AS UNSIGNED)))",
        'search' => ['examtitle'],
        'order' => 'exam_date DESC, id DESC',
        'published' => 'status = 1',
    ],

    'omr-exams' => [
        'exams' => ['kpsc'],
        'label' => 'OMR exam',
        'table' => 'kpsc_daily_exams',
        'where' => ['section' => 'Model Exam'],
        'title' => 'examtitle',
        'subtitle' => "CONCAT_WS(' · ', DATE_FORMAT(exam_date, '%d %b %Y'), category)",
        'search' => ['examtitle', 'category'],
        'order' => 'exam_date DESC, id DESC',
        'published' => 'status = 1',
    ],

    'free-classes' => [
        'exams' => ['kpsc'],
        'label' => 'Free class category',
        'table' => 'class_category_free',
        'where' => [],
        'title' => 'category_name',
        'subtitle' => "'free classes'",
        'search' => ['category_name'],
        'order' => 'position ASC, id ASC',
        'published' => 'status = 1',
    ],

    'paid-classes' => [
        'exams' => ['kpsc'],
        'label' => 'Paid class category',
        'table' => 'class_category_paid',
        'where' => [],
        'title' => 'category_name',
        'subtitle' => "'paid classes'",
        'search' => ['category_name'],
        'order' => 'position ASC, id ASC',
        'published' => 'status = 1',
    ],

    'psc-bulletin' => [
        'exams' => ['kpsc'],
        'label' => 'PSC bulletin issue',
        'table' => 'bulletin',
        'where' => [],
        'title' => "CONCAT('PSC Bulletin · ', DATE_FORMAT(month_year, '%b %Y'), ' · Issue ', issue)",
        'subtitle' => "DATE_FORMAT(month_year, '%M %Y')",
        'search' => ['issue'],
        'order' => 'month_year DESC, issue DESC',
        'published' => 'status = 1',
    ],

];
