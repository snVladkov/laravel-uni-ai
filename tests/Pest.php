<?php

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Seed the application's roles and permissions.
 */
function seedRoles(): void
{
    test()->seed(RoleAndPermissionSeeder::class);
}

/**
 * Create an authenticated user with the given role.
 */
function userWithRole(string $role): User
{
    if (Role::query()->count() === 0) {
        seedRoles();
    }

    $factory = User::factory();

    $user = match ($role) {
        'administrator' => $factory->administrator(),
        'head_of_department' => $factory->headOfDepartment('Информатика'),
        'teacher' => $factory->teacher('Информатика'),
        'student' => $factory->student(),
        default => throw new InvalidArgumentException("Unknown role [{$role}]."),
    };

    return $user->create();
}
