<?php

/*
 | Permissions added for the exam-type modules, shown on the Staff Access page. An Admin (user type "Admin") can do everything.
 | Anyone else needs the permission (from a role, or ticked for them directly) AND access to the exam type (Staff Access page).
 |
 | "exam_scoped" : the permission only works inside the exam types the person has access to.
 | "roles"       : roles that receive the permission when it is first created (so nobody loses what they could already do).
 */
return [
    'groups' => [
        'Learning' => [
            'Exam courses manage' => ['label' => 'Categories and courses', 'hint' => 'Create / edit categories, courses, course exams, course content and course classes.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Question library manage' => ['label' => 'Question library', 'hint' => 'Folders, questions, imports.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Class library manage' => ['label' => 'Class library', 'hint' => 'Class folders, classes, cloud storage browser.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Roaring study manage' => ['label' => 'Roaring Study', 'hint' => 'Create and publish study packs.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
        ],
        'Sales' => [
            'New joins view' => ['label' => 'New joins list', 'hint' => 'See newly joined students (for the sales team).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Developer']],
            'New joins mark' => ['label' => 'Mark / unmark new joins', 'hint' => 'Mark a new join as contacted, with a note.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Developer']],
            'Admissions view' => ['label' => 'See admissions and payments', 'hint' => 'Admissions list, details, transactions, payment links list.', 'exam_scoped' => true, 'roles' => []],
            'Admissions manage' => ['label' => 'Add / cancel admissions', 'hint' => 'Add a student to a course, record money, cancel, refund, remove.', 'exam_scoped' => true, 'roles' => []],
            'Payment links manage' => ['label' => 'Payment links', 'hint' => 'Create, send and cancel payment links.', 'exam_scoped' => true, 'roles' => []],
            'Revenue view' => ['label' => 'Revenue and commission analytics', 'hint' => 'Money figures, commission, monthly revenue.', 'exam_scoped' => true, 'roles' => []],
        ],
        'Content' => [
            'Content manage' => ['label' => 'Banners, What\'s New, Announcements, Articles, Notifications', 'hint' => 'Add and edit the home-page content shown for each exam type.', 'exam_scoped' => false, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
        ],
        'Communication' => [
            'Staff chat use' => ['label' => 'Chats', 'hint' => 'Chat with other staff and join staff groups.', 'exam_scoped' => false, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
        ],
        'Administration' => [
            'Activity log view' => ['label' => 'Staff activity log', 'hint' => 'See what every staff member did.', 'exam_scoped' => false, 'roles' => []],
            'Trash manage' => ['label' => 'Trash (restore deleted items)', 'hint' => 'Restore or permanently delete items that were deleted.', 'exam_scoped' => true, 'roles' => []],
            'Staff access manage' => ['label' => 'Staff access', 'hint' => 'Give staff roles, permissions and exam types.', 'exam_scoped' => false, 'roles' => []],
        ],
    ],

    /* How long notifications stay in the bell, and the activity log kept before it may be cleared. */
    'notification_days' => 30,
    'activity_log_days' => 365,
];
