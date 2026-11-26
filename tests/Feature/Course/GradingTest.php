<?php

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

function enrolledCourse(): array
{
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->forTeacher($teacher)->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    return [$teacher, $student, $course];
}

it('grades a student with a valid 0.50 step and derives the letter band', function (float $score, string $letter) {
    [$teacher, $student, $course] = enrolledCourse();

    $this->actingAs($teacher)
        ->patch("/courses/{$course->id}/students/{$student->id}/grade", ['grade' => $score])
        ->assertRedirect();

    $enrollment = CourseEnrollment::query()->sole();

    expect((float) $enrollment->grade)->toBe($score)
        ->and($enrollment->grade_letter)->toBe($letter)
        ->and($enrollment->graded_by)->toBe($teacher->id)
        ->and($enrollment->graded_at)->not->toBeNull();
})->with([
    [2.0, 'F'],
    [2.5, 'F'],
    [3.0, 'D'],
    [3.5, 'D'],
    [4.0, 'C'],
    [4.5, 'C'],
    [5.0, 'B'],
    [5.5, 'B'],
    [6.0, 'A'],
]);

it('rejects grades outside the 2.00-6.00 range', function (float $score) {
    [$teacher, $student, $course] = enrolledCourse();

    $this->actingAs($teacher)
        ->patch("/courses/{$course->id}/students/{$student->id}/grade", ['grade' => $score])
        ->assertSessionHasErrors('grade');

    expect(CourseEnrollment::query()->sole()->grade)->toBeNull();
})->with([1.5, 6.5]);

it('rejects grades that are not multiples of 0.50', function (float $score) {
    [$teacher, $student, $course] = enrolledCourse();

    $this->actingAs($teacher)
        ->patch("/courses/{$course->id}/students/{$student->id}/grade", ['grade' => $score])
        ->assertSessionHasErrors('grade');

    expect(CourseEnrollment::query()->sole()->grade)->toBeNull();
})->with([5.25, 4.75, 3.1]);

it('blocks another teacher from grading students in the course', function () {
    [, $student, $course] = enrolledCourse();
    $other = User::factory()->teacher()->create();

    $this->actingAs($other)
        ->patch("/courses/{$course->id}/students/{$student->id}/grade", ['grade' => 5.0])
        ->assertForbidden();
});

it('blocks students from grading themselves', function () {
    [, $student, $course] = enrolledCourse();

    $this->actingAs($student)
        ->patch("/courses/{$course->id}/students/{$student->id}/grade", ['grade' => 6.0])
        ->assertForbidden();
});

it('blocks grading a course that has not been approved', function () {
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->forTeacher($teacher)->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($teacher)
        ->patch("/courses/{$course->id}/students/{$student->id}/grade", ['grade' => 5.0])
        ->assertForbidden();
});

it('shows students their own grade but never another student grade', function () {
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();
    $otherStudent = User::factory()->student()->create();
    $course = Course::factory()->forTeacher($teacher)->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);
    $course->students()->attach($otherStudent->id, ['enrolled_at' => now()]);

    $course->enrollments()->where('student_id', $otherStudent->id)->update([
        'grade' => 5.5,
        'grade_letter' => 'B',
        'graded_by' => $teacher->id,
        'graded_at' => now(),
    ]);

    $this->actingAs($student)
        ->get("/courses/{$course->id}")
        ->assertOk()
        ->assertSee(__('courses.show.not_graded'))
        ->assertDontSee($otherStudent->name);

    $course->enrollments()->where('student_id', $student->id)->update([
        'grade' => 4.5,
        'grade_letter' => 'C',
        'graded_by' => $teacher->id,
        'graded_at' => now(),
    ]);

    $this->actingAs($student)
        ->get("/courses/{$course->id}")
        ->assertOk()
        ->assertSee('4.50')
        ->assertDontSee($otherStudent->name)
        ->assertDontSee('5.50');
});
