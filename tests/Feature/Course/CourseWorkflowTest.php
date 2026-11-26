<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('lets a teacher create a draft course in their department', function () {
    $teacher = User::factory()->teacher('Информатика')->create();

    $this->actingAs($teacher)->get('/courses/create')->assertOk();

    $this->actingAs($teacher)
        ->post('/courses', [
            'title' => 'Микроикономика',
            'code' => 'eco101',
            'description' => 'Въведение в микроикономиката.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('courses', [
        'title' => 'Микроикономика',
        'code' => 'ECO101',
        'status' => 'draft',
        'teacher_id' => $teacher->id,
        'department' => 'Информатика',
    ]);
});

it('validates required course fields and unique codes', function () {
    $teacher = User::factory()->teacher()->create();
    Course::factory()->forTeacher($teacher)->create(['code' => 'ECO101']);

    $this->actingAs($teacher)->post('/courses', [])->assertSessionHasErrors(['title', 'code']);

    $this->actingAs($teacher)
        ->post('/courses', ['title' => 'Дубликат', 'code' => 'ECO101'])
        ->assertSessionHasErrors('code');
});

it('lets the owning teacher edit a draft or rejected course only', function () {
    $teacher = User::factory()->teacher()->create();
    $draft = Course::factory()->forTeacher($teacher)->create(['status' => CourseStatus::Draft]);
    $approved = Course::factory()->forTeacher($teacher)->approved()->create();

    $this->actingAs($teacher)
        ->put("/courses/{$draft->id}", [
            'title' => 'Обновено заглавие',
            'code' => $draft->code,
        ])
        ->assertRedirect();

    expect($draft->fresh()->title)->toBe('Обновено заглавие');

    $this->actingAs($teacher)
        ->put("/courses/{$approved->id}", [
            'title' => 'Неуспешна промяна',
            'code' => $approved->code,
        ])
        ->assertForbidden();
});

it('lets the owning teacher submit a draft for approval', function () {
    $teacher = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($teacher)->create(['status' => CourseStatus::Draft]);

    $this->actingAs($teacher)
        ->post("/courses/{$course->id}/submit-approval")
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::PendingApproval);
});

it('shows pending courses from the head of department own department only', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $own = Course::factory()->pendingApproval()->create(['department' => 'Информатика']);
    $other = Course::factory()->pendingApproval()->create(['department' => 'Финанси']);

    $this->actingAs($hod)
        ->get('/hod/courses/pending')
        ->assertOk()
        ->assertSee($own->title)
        ->assertDontSee($other->title);
});

it('lets the head of department approve a pending course', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $course = Course::factory()->pendingApproval()->create(['department' => 'Информатика']);

    $this->actingAs($hod)
        ->post("/hod/courses/{$course->id}/approve")
        ->assertRedirect();

    $course->refresh();

    expect($course->status)->toBe(CourseStatus::Approved)
        ->and($course->approved_by)->toBe($hod->id)
        ->and($course->approved_at)->not->toBeNull()
        ->and($course->rejection_reason)->toBeNull();
});

it('lets the head of department reject a pending course with a reason', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $course = Course::factory()->pendingApproval()->create(['department' => 'Информатика']);

    $this->actingAs($hod)
        ->post("/hod/courses/{$course->id}/reject", ['rejection_reason' => 'Липсва учебен план.'])
        ->assertRedirect();

    $course->refresh();

    expect($course->status)->toBe(CourseStatus::Rejected)
        ->and($course->rejection_reason)->toBe('Липсва учебен план.');
});

it('requires a rejection reason', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $course = Course::factory()->pendingApproval()->create(['department' => 'Информатика']);

    $this->actingAs($hod)
        ->post("/hod/courses/{$course->id}/reject", ['rejection_reason' => ''])
        ->assertSessionHasErrors('rejection_reason');

    expect($course->fresh()->status)->toBe(CourseStatus::PendingApproval);
});

it('lets the head of department mark an approved course as completed', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $course = Course::factory()->approved()->create(['department' => 'Информатика']);

    $this->actingAs($hod)
        ->post("/hod/courses/{$course->id}/complete")
        ->assertRedirect();

    expect($course->fresh()->status)->toBe(CourseStatus::Completed);
});

it('blocks a head of department from reviewing another department course', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $course = Course::factory()->pendingApproval()->create(['department' => 'Финанси']);

    $this->actingAs($hod)->post("/hod/courses/{$course->id}/approve")->assertForbidden();
    $this->actingAs($hod)->post("/hod/courses/{$course->id}/complete")->assertForbidden();
});

it('blocks teachers and administrators from approving courses', function () {
    $course = Course::factory()->pendingApproval()->create(['department' => 'Информатика']);
    $teacher = User::factory()->teacher('Информатика')->create();
    $admin = User::factory()->administrator()->create();

    $this->actingAs($teacher)->post("/hod/courses/{$course->id}/approve")->assertForbidden();
    $this->actingAs($admin)->post("/hod/courses/{$course->id}/approve")->assertForbidden();
});

it('applies course visibility rules', function () {
    $owner = User::factory()->teacher('Информатика')->create();
    $otherTeacher = User::factory()->teacher('Информатика')->create();
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    $otherHod = User::factory()->headOfDepartment('Финанси')->create();
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();
    $outsider = User::factory()->student()->create();

    $approved = Course::factory()->forTeacher($owner)->approved()->create(['department' => 'Информатика']);
    $approved->students()->attach($student->id, ['enrolled_at' => now()]);

    $draft = Course::factory()->forTeacher($owner)->create(['status' => CourseStatus::Draft, 'department' => 'Информатика']);

    $this->actingAs($owner)->get("/courses/{$draft->id}")->assertOk();
    $this->actingAs($otherTeacher)->get("/courses/{$draft->id}")->assertForbidden();
    $this->actingAs($hod)->get("/courses/{$draft->id}")->assertOk();
    $this->actingAs($otherHod)->get("/courses/{$draft->id}")->assertForbidden();
    $this->actingAs($admin)->get("/courses/{$draft->id}")->assertOk();

    $this->actingAs($student)->get("/courses/{$approved->id}")->assertOk();
    $this->actingAs($student)->get("/courses/{$draft->id}")->assertForbidden();
    $this->actingAs($outsider)->get("/courses/{$approved->id}")->assertForbidden();
});

it('redirects guests away from course pages', function () {
    $course = Course::factory()->approved()->create();

    $this->get("/courses/{$course->id}")->assertRedirect('/login');
});

it('filters the course index by role', function () {
    $teacher = User::factory()->teacher('Информатика')->create();
    $own = Course::factory()->forTeacher($teacher)->create(['title' => 'Собствена дисциплина']);
    $foreign = Course::factory()->create(['title' => 'Чужда дисциплина']);

    $this->actingAs($teacher)
        ->get('/courses')
        ->assertOk()
        ->assertSee($own->title)
        ->assertDontSee($foreign->title);

    $student = User::factory()->student()->create();
    $enrolled = Course::factory()->approved()->create(['title' => 'Записана дисциплина']);
    $enrolled->students()->attach($student->id, ['enrolled_at' => now()]);
    $notEnrolled = Course::factory()->approved()->create(['title' => 'Незаписана дисциплина']);

    $this->actingAs($student)
        ->get('/courses')
        ->assertOk()
        ->assertSee($enrolled->title)
        ->assertDontSee($notEnrolled->title);
});
