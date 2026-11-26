<?php

return [
    'index' => [
        'title' => 'Teachers',
        'subtitle' => 'Manage the teachers in your department.',
        'new' => 'New teacher',
        'empty' => 'There are no teachers in your department yet.',
        'empty_cta' => 'Add the first teacher',
        'department_scope' => 'Showing teachers from :department only.',
    ],
    'create' => [
        'title' => 'Add a teacher',
        'subtitle' => 'The teacher is assigned to your department automatically.',
    ],
    'edit' => [
        'title' => 'Edit teacher',
        'subtitle' => 'Leave the password fields empty to keep the current password.',
    ],
    'fields' => [
        'name' => 'Full name',
        'email' => 'Email',
        'teacher_code' => 'Teacher number',
        'department' => 'Department',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'password_optional' => 'New password (optional)',
        'courses' => 'Courses',
    ],
    'delete' => [
        'title' => 'Delete teacher',
        'warning' => 'Deleting this teacher also deletes all their courses, materials and enrolments. This cannot be undone.',
        'confirm' => 'Delete this teacher and all their courses?',
    ],
    'flash' => [
        'created' => 'Teacher :name was created.',
        'updated' => 'Teacher :name was updated.',
        'deleted' => 'Teacher :name was deleted together with their courses.',
    ],
];
