<?php

use App\Enums\GradeBand;
use App\Models\Course;
use App\Models\User;
use App\Services\AcademicTranscriptService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function enrollWithGrade(User $student, ?float $grade): Course
{
    $course = Course::factory()->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    if ($grade !== null) {
        $course->enrollments()->where('student_id', $student->id)->update([
            'grade' => $grade,
            'grade_letter' => GradeBand::fromScore($grade)->letter(),
            'graded_at' => now(),
        ]);
    }

    return $course;
}

it('builds a snapshot with a rounded arithmetic average of graded courses only', function () {
    seedRoles();

    $student = User::factory()->student()->create();
    enrollWithGrade($student, 4.5);
    enrollWithGrade($student, 5.0);
    enrollWithGrade($student, 5.5);
    enrollWithGrade($student, null);

    $snapshot = app(AcademicTranscriptService::class)->buildSnapshot($student);

    expect($snapshot)->toHaveKeys(['student', 'courses', 'average', 'generated_at'])
        ->and($snapshot['courses'])->toHaveCount(3)
        ->and($snapshot['average'])->toBe(5.0)
        ->and($snapshot['student'])->toHaveKeys(['name', 'email', 'student_code'])
        ->and($snapshot['courses'][0])->toHaveKeys(['code', 'title', 'department', 'grade', 'grade_letter', 'graded_at']);
});

it('returns a null average when the student has no graded courses', function () {
    seedRoles();

    $student = User::factory()->student()->create();
    enrollWithGrade($student, null);

    $snapshot = app(AcademicTranscriptService::class)->buildSnapshot($student);

    expect($snapshot['courses'])->toBe([])
        ->and($snapshot['average'])->toBeNull();
});
