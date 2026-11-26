<?php

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedRoles();
    Storage::fake('local');
});

it('shows only teachers from the head of department department', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $own = User::factory()->teacher('Информатика')->create(['name' => 'Иван Иванов']);
    $other = User::factory()->teacher('Финанси')->create(['name' => 'Мария Петрова']);

    $this->actingAs($hod)
        ->get('/hod/teachers')
        ->assertOk()
        ->assertSee($own->name)
        ->assertDontSee($other->name);
});

it('lets the head of department create a teacher in their own department', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();

    $this->actingAs($hod)->get('/hod/teachers/create')->assertOk();

    $this->actingAs($hod)
        ->post('/hod/teachers', [
            'name' => 'Нов Преподавател',
            'email' => 'new.teacher@uev.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'teacher_code' => 'T-9001',
            'department' => 'Финанси',
        ])
        ->assertRedirect('/hod/teachers');

    $teacher = User::query()->where('email', 'new.teacher@uev.test')->sole();

    expect($teacher->hasRole('teacher'))->toBeTrue()
        ->and($teacher->department)->toBe('Информатика')
        ->and($teacher->teacher_code)->toBe('T-9001');
});

it('validates unique email and confirmed password when creating a teacher', function () {
    $hod = User::factory()->headOfDepartment()->create();
    $existing = User::factory()->teacher()->create();

    $this->actingAs($hod)
        ->post('/hod/teachers', [
            'name' => 'Дубликат',
            'email' => $existing->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors(['email', 'password']);
});

it('lets the head of department update a teacher without changing the password', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $teacher = User::factory()->teacher('Информатика')->create();
    $originalPassword = $teacher->password;

    $this->actingAs($hod)->get("/hod/teachers/{$teacher->id}/edit")->assertOk();

    $this->actingAs($hod)
        ->put("/hod/teachers/{$teacher->id}", [
            'name' => 'Обновено Име',
            'email' => $teacher->email,
        ])
        ->assertRedirect('/hod/teachers');

    $teacher->refresh();

    expect($teacher->name)->toBe('Обновено Име')
        ->and($teacher->password)->toBe($originalPassword)
        ->and($teacher->department)->toBe('Информатика');
});

it('blocks the head of department from managing teachers of another department', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $foreign = User::factory()->teacher('Финанси')->create();

    $this->actingAs($hod)->get("/hod/teachers/{$foreign->id}/edit")->assertForbidden();
    $this->actingAs($hod)->put("/hod/teachers/{$foreign->id}", ['name' => 'X', 'email' => $foreign->email])->assertForbidden();
    $this->actingAs($hod)->delete("/hod/teachers/{$foreign->id}")->assertForbidden();
});

it('deletes a teacher together with their courses and stored material files', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $teacher = User::factory()->teacher('Информатика')->create();
    $course = Course::factory()->forTeacher($teacher)->create();

    Storage::disk('local')->put("course-materials/{$course->id}/notes.pdf", 'contents');

    CourseMaterial::factory()->create([
        'course_id' => $course->id,
        'uploaded_by' => $teacher->id,
        'file_path' => "course-materials/{$course->id}/notes.pdf",
        'external_url' => null,
    ]);

    $this->actingAs($hod)
        ->delete("/hod/teachers/{$teacher->id}")
        ->assertRedirect('/hod/teachers');

    $this->assertModelMissing($teacher);
    $this->assertModelMissing($course);
    Storage::disk('local')->assertMissing("course-materials/{$course->id}/notes.pdf");
});

it('blocks teachers and administrators from teacher management', function () {
    $teacher = User::factory()->teacher()->create();
    $admin = User::factory()->administrator()->create();
    $target = User::factory()->teacher()->create();

    $this->actingAs($teacher)->get('/hod/teachers')->assertForbidden();
    $this->actingAs($admin)->get('/hod/teachers')->assertForbidden();
    $this->actingAs($teacher)->delete("/hod/teachers/{$target->id}")->assertForbidden();
});

it('redirects guests from teacher management to login', function () {
    $this->get('/hod/teachers')->assertRedirect('/login');
});
