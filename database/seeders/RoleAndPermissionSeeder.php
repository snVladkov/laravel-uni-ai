<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Role to permission matrix.
     *
     * @var array<string, array<int, string>>
     */
    public array $matrix = [
        'student' => [
            'materials.view',
            'transcripts.view',
        ],
        'teacher' => [
            'courses.create',
            'courses.update.own',
            'courses.submit',
            'courses.grade.own',
            'materials.manage.own',
            'materials.view',
        ],
        'head_of_department' => [
            'courses.review',
            'teachers.manage',
            'materials.view',
            'transcripts.view',
        ],
        'administrator' => [
            'courses.enroll_students',
            'users.manage',
            'transcripts.create',
            'transcripts.view',
            'materials.view',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect($this->matrix)
            ->flatten()
            ->unique()
            ->values();

        $permissions->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        foreach ($this->matrix as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
