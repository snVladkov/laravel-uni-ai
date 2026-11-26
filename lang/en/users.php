<?php

return [
    'index' => [
        'title' => 'Users',
        'subtitle' => 'Create accounts and manage their roles and departments.',
        'new' => 'New user',
        'empty' => 'No users match your filters.',
        'search_placeholder' => 'Search by name, email or number…',
        'role_filter' => 'Role',
        'all_roles' => 'All roles',
        'department' => 'Department',
        'sync_roles' => 'Update roles',
        'transcripts' => 'Transcripts',
    ],
    'create' => [
        'title' => 'Create a user',
        'subtitle' => 'Choose one or more roles. Teacher and head of department accounts need a department.',
    ],
    'fields' => [
        'roles' => 'Roles',
        'department' => 'Department',
        'department_hint' => 'Required for teachers and heads of department.',
        'teacher_code' => 'Teacher number',
        'student_code' => 'Student number',
    ],
    'last_admin' => 'You cannot remove the administrator role from the last administrator account.',
    'flash' => [
        'created' => 'User :name was created.',
        'roles_updated' => 'Roles for :name were updated.',
    ],
];
