<?php

use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('lets an administrator enroll a student in an approved course', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->approved()->create();

    $this->actingAs($admin)
        ->post("/courses/{$course->id}/students", ['student_id' => $student->id])
        ->assertRedirect();

    $this->assertDatabaseHas('course_enrollments', [
        'course_id' => $course->id,
        'student_id' => $student->id,
        'enrolled_by' => $admin->id,
    ]);
});

it('rejects duplicate enrollments', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($admin)
        ->post("/courses/{$course->id}/students", ['student_id' => $student->id])
        ->assertSessionHasErrors('student_id');

    expect($course->enrollments()->count())->toBe(1);
});

it('rejects enrolling a user without the student role', function () {
    $admin = User::factory()->administrator()->create();
    $teacher = User::factory()->teacher()->create();
    $course = Course::factory()->approved()->create();

    $this->actingAs($admin)
        ->post("/courses/{$course->id}/students", ['student_id' => $teacher->id])
        ->assertSessionHasErrors('student_id');

    $this->assertDatabaseCount('course_enrollments', 0);
});

it('blocks enrollment in a course that is not approved', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->create();

    $this->actingAs($admin)
        ->post("/courses/{$course->id}/students", ['student_id' => $student->id])
        ->assertForbidden();

    $this->assertDatabaseCount('course_enrollments', 0);
});

it('blocks non-administrators from enrolling students', function () {
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->forTeacher($teacher)->approved()->create();

    $this->actingAs($teacher)
        ->post("/courses/{$course->id}/students", ['student_id' => $student->id])
        ->assertForbidden();

    $this->actingAs($student)
        ->post("/courses/{$course->id}/students", ['student_id' => $student->id])
        ->assertForbidden();
});

it('lets an administrator remove an enrollment', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($admin)
        ->delete("/courses/{$course->id}/students/{$student->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('course_enrollments', [
        'course_id' => $course->id,
        'student_id' => $student->id,
    ]);
});
