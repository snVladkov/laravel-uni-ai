<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    seedRoles();
});

it('lets an administrator browse and filter users', function () {
    $admin = User::factory()->administrator()->create();
    $teacher = User::factory()->teacher('Информатика')->create(['name' => 'Иван Преподавателя']);
    $student = User::factory()->student()->create(['name' => 'Мария Студентова']);

    $this->actingAs($admin)
        ->get('/admin/users')
        ->assertOk()
        ->assertSee($teacher->name)
        ->assertSee($student->name);

    $this->actingAs($admin)
        ->get('/admin/users?role=teacher')
        ->assertOk()
        ->assertSee($teacher->name)
        ->assertDontSee($student->name);
});

it('lets an administrator create a teacher with a department', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)->get('/admin/users/create')->assertOk();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Нов Учител',
            'email' => 'new.teacher@uev.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'roles' => ['teacher'],
            'department' => 'Информатика',
            'teacher_code' => 'T-7001',
        ])
        ->assertRedirect('/admin/users');

    $user = User::query()->where('email', 'new.teacher@uev.test')->sole();

    expect($user->hasRole('teacher'))->toBeTrue()
        ->and($user->department)->toBe('Информатика')
        ->and($user->teacher_code)->toBe('T-7001');
});

it('requires a department when the teacher or head of department role is selected', function () {
    $admin = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Без Катедра',
            'email' => 'no.department@uev.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'roles' => ['teacher'],
        ])
        ->assertSessionHasErrors('department');

    $this->actingAs($admin)
        ->post('/admin/users', [
            'name' => 'Без Роля',
            'email' => 'no.role@uev.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'roles' => [],
        ])
        ->assertSessionHasErrors('roles');
});

it('lets an administrator sync the roles of a user', function () {
    $admin = User::factory()->administrator()->create();
    $user = User::factory()->student()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$user->id}/roles", ['roles' => ['teacher', 'head_of_department'], 'department' => 'Финанси'])
        ->assertRedirect();

    $user->refresh();

    expect($user->hasRole('teacher'))->toBeTrue()
        ->and($user->hasRole('head_of_department'))->toBeTrue()
        ->and($user->hasRole('student'))->toBeFalse()
        ->and($user->department)->toBe('Финанси');
});

it('prevents removing the administrator role from the last administrator', function () {
    $admin = User::factory()->administrator()->create();

    expect(Role::findByName('administrator')->users()->count())->toBe(1);

    $this->actingAs($admin)
        ->patch("/admin/users/{$admin->id}/roles", ['roles' => ['student']])
        ->assertSessionHasErrors('roles');

    expect($admin->fresh()->hasRole('administrator'))->toBeTrue();
});

it('allows removing the administrator role when another administrator remains', function () {
    $admin = User::factory()->administrator()->create();
    $other = User::factory()->administrator()->create();

    $this->actingAs($admin)
        ->patch("/admin/users/{$other->id}/roles", ['roles' => ['student']])
        ->assertRedirect();

    expect($other->fresh()->hasRole('administrator'))->toBeFalse()
        ->and($other->fresh()->hasRole('student'))->toBeTrue();
});

it('blocks non-administrators from user management', function () {
    $teacher = User::factory()->teacher()->create();
    $hod = User::factory()->headOfDepartment()->create();
    $target = User::factory()->student()->create();

    $this->actingAs($teacher)->get('/admin/users')->assertForbidden();
    $this->actingAs($hod)->get('/admin/users')->assertForbidden();
    $this->actingAs($teacher)->patch("/admin/users/{$target->id}/roles", ['roles' => ['teacher']])->assertForbidden();
});

it('redirects guests from user management to login', function () {
    $this->get('/admin/users')->assertRedirect('/login');
});
