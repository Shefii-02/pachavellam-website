<?php

/*
 | Permissions added for the exam-type modules, shown on the Staff Access page. An Admin (user type "Admin") can do everything.
 | Anyone else needs the permission (from a role, or ticked for them directly) AND access to the exam type (Staff Access page).
 |
 | "feature" / "level": the View and Manage ticks of one feature share a "feature"; Manage always includes View.
 | "exam_scoped" : the permission only works inside the exam types the person has access to.
 | "roles"       : roles that receive the permission when it is first created (so nobody loses what they could already do).
 */
return [
    'groups' => [
        'Learning' => [
            'Exam courses view' => ['feature' => 'Categories and courses', 'level' => 'view', 'hint' => 'Open categories, courses, course exams, content and classes (read only).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Exam courses manage' => ['feature' => 'Categories and courses', 'level' => 'manage', 'hint' => 'Create / edit categories, courses, course exams (daily, OMR, special), course content and course classes.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Question library view' => ['feature' => 'Question library', 'level' => 'view', 'hint' => 'Open folders and questions (read only).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Question library manage' => ['feature' => 'Question library', 'level' => 'manage', 'hint' => 'Add / edit / delete folders and questions, imports.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Class library view' => ['feature' => 'Class library', 'level' => 'view', 'hint' => 'Open class folders and classes (read only).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Class library manage' => ['feature' => 'Class library', 'level' => 'manage', 'hint' => 'Add / edit / delete class folders and classes, cloud storage browser.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Roaring study view' => ['feature' => 'Roaring Study', 'level' => 'view', 'hint' => 'Open study packs (read only).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Roaring study manage' => ['feature' => 'Roaring Study', 'level' => 'manage', 'hint' => 'Create, edit and publish study packs.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
        ],
        'Sales' => [
            'New joins view' => ['feature' => 'New joins', 'level' => 'view', 'hint' => 'See newly joined students (for the sales team).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Developer']],
            'New joins mark' => ['feature' => 'New joins', 'level' => 'manage', 'hint' => 'Mark / unmark a new join as contacted, with a note.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Developer']],
            'Admissions view' => ['feature' => 'Admissions and payments', 'level' => 'view', 'hint' => 'Admissions list, details, transactions.', 'exam_scoped' => true, 'roles' => []],
            'Admissions manage' => ['feature' => 'Admissions and payments', 'level' => 'manage', 'hint' => 'Add a student to a course, record money, cancel, refund, remove.', 'exam_scoped' => true, 'roles' => []],
            'Payment links view' => ['feature' => 'Payment links', 'level' => 'view', 'hint' => 'See the payment links list.', 'exam_scoped' => true, 'roles' => []],
            'Payment links manage' => ['feature' => 'Payment links', 'level' => 'manage', 'hint' => 'Create, send and cancel payment links.', 'exam_scoped' => true, 'roles' => []],
            'Revenue view' => ['feature' => 'Revenue and commission analytics', 'level' => 'view', 'hint' => 'Money figures, commission, monthly revenue.', 'exam_scoped' => true, 'roles' => []],
        ],
        'Content' => [
            'Content view' => ['feature' => 'Banners, What\'s New, Announcements, Articles, Notifications', 'level' => 'view', 'hint' => 'See the home-page content of each exam type (read only).', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
            'Content manage' => ['feature' => 'Banners, What\'s New, Announcements, Articles, Notifications', 'level' => 'manage', 'hint' => 'Add and edit the home-page content shown for each exam type.', 'exam_scoped' => true, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
        ],
        'Communication' => [
            'Staff chat use' => ['feature' => 'Chats', 'level' => 'manage', 'hint' => 'Chat with other staff and join staff groups.', 'exam_scoped' => false, 'roles' => ['Office Staff', 'Data Entry', 'Developer']],
        ],
        'Administration' => [
            'Activity log view' => ['feature' => 'Staff activity log', 'level' => 'view', 'hint' => 'See what every staff member did.', 'exam_scoped' => false, 'roles' => []],
            'Trash view' => ['feature' => 'Trash', 'level' => 'view', 'hint' => 'See the items that were deleted.', 'exam_scoped' => true, 'roles' => []],
            'Trash manage' => ['feature' => 'Trash', 'level' => 'manage', 'hint' => 'Restore or permanently delete items that were deleted.', 'exam_scoped' => true, 'roles' => []],
            'Staff access view' => ['feature' => 'Staff access', 'level' => 'view', 'hint' => 'See the roles, permissions and exam types of each staff member.', 'exam_scoped' => false, 'roles' => []],
            'Staff access manage' => ['feature' => 'Staff access', 'level' => 'manage', 'hint' => 'Give staff roles, permissions and exam types.', 'exam_scoped' => false, 'roles' => []],
        ],
    ],

    /*
 | The older per exam type screens (admin/kpsc/..., admin/rrb/... and so on) are guarded by the first part of the address after the exam type:
 |   open     : no permission needed (dashboard, interested students)
 |   sections : address part => feature ("Exam courses" or "Content"); every other part uses "default"
 */
    'modules' => [
        'open' => ['', 'interested-students'],
        'default' => 'Content',
        'sections' => [
            'daily-exam' => 'Exam courses', 'ca-daily-exam' => 'Exam courses', 'model-exam' => 'Exam courses', 'exam-tools' => 'Exam courses',
            'exam-instructions' => 'Exam courses', 'exam-reports' => 'Exam courses',
            'free-class' => 'Class library', 'paid-class' => 'Class library', 'free-class-list' => 'Class library', 'paid-class-list' => 'Class library',
            'free_category_class' => 'Class library', 'free_subcategory_class' => 'Class library', 'paid_category_class' => 'Class library', 'paid_subcategory_class' => 'Class library',
            'new-qa' => 'Question library',
        ],
    ],

    /* How long notifications stay in the bell, and the activity log kept before it may be cleared. */
    'notification_days' => 30,
    'activity_log_days' => 365,
];
