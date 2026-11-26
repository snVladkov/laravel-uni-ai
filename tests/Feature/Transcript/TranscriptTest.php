<?php

use App\Models\AcademicTranscript;
use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

function gradedEnrollment(User $student, float $grade, string $letter, array $courseAttributes = []): Course
{
    $course = Course::factory()->approved()->create($courseAttributes);
    $course->students()->attach($student->id, ['enrolled_at' => now()]);
    $course->enrollments()->where('student_id', $student->id)->update([
        'grade' => $grade,
        'grade_letter' => $letter,
        'graded_at' => now(),
    ]);

    return $course;
}

it('shows a student transcript list to an administrator', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create(['name' => 'Иван Студентов']);
    $transcript = AcademicTranscript::factory()->create([
        'student_id' => $student->id,
        'generated_by' => $admin->id,
        'snapshot_data' => ['student' => ['name' => $student->name], 'courses' => []],
    ]);

    $this->actingAs($admin)
        ->get("/admin/students/{$student->id}/transcripts")
        ->assertOk()
        ->assertSee($student->name)
        ->assertSee($transcript->transcript_number);
});

it('generates a transcript with graded courses and the arithmetic average', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();

    gradedEnrollment($student, 4.5, 'C', ['title' => 'Микроикономика', 'code' => 'ECO101']);
    gradedEnrollment($student, 5.5, 'B', ['title' => 'Макроикономика', 'code' => 'ECO102']);

    $ungraded = Course::factory()->approved()->create(['title' => 'Без оценка']);
    $ungraded->students()->attach($student->id, ['enrolled_at' => now()]);

    $this->actingAs($admin)
        ->post("/admin/students/{$student->id}/transcripts")
        ->assertRedirect();

    $transcript = AcademicTranscript::query()->sole();

    expect($transcript->student_id)->toBe($student->id)
        ->and($transcript->generated_by)->toBe($admin->id)
        ->and($transcript->transcript_number)->toStartWith('UEV-'.now()->year.'-')
        ->and((float) $transcript->gpa)->toBe(5.0)
        ->and($transcript->snapshot_data['courses'])->toHaveCount(2)
        ->and($transcript->snapshot_data['student']['name'])->toBe($student->name)
        ->and(collect($transcript->snapshot_data['courses'])->pluck('code')->all())
        ->toContain('ECO101', 'ECO102')
        ->not->toContain($ungraded->code);
});

it('generates an empty transcript without grades', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create();

    $this->actingAs($admin)
        ->post("/admin/students/{$student->id}/transcripts")
        ->assertRedirect();

    $transcript = AcademicTranscript::query()->sole();

    expect($transcript->gpa)->toBeNull()
        ->and($transcript->snapshot_data['courses'])->toBe([]);
});

it('assigns unique sequential transcript numbers', function () {
    $admin = User::factory()->administrator()->create();
    $first = User::factory()->student()->create();
    $second = User::factory()->student()->create();

    $this->actingAs($admin)->post("/admin/students/{$first->id}/transcripts")->assertRedirect();
    $this->actingAs($admin)->post("/admin/students/{$second->id}/transcripts")->assertRedirect();

    $numbers = AcademicTranscript::query()->orderBy('id')->pluck('transcript_number');

    expect($numbers->unique())->toHaveCount(2)
        ->and($numbers->first())->toBe('UEV-'.now()->year.'-0001')
        ->and($numbers->last())->toBe('UEV-'.now()->year.'-0002');
});

it('renders a printable transcript for an administrator', function () {
    $admin = User::factory()->administrator()->create();
    $student = User::factory()->student()->create(['name' => 'Мария Студентова']);

    gradedEnrollment($student, 6.0, 'A', ['title' => 'Икономика на труда']);

    $this->actingAs($admin)->post("/admin/students/{$student->id}/transcripts")->assertRedirect();

    $transcript = AcademicTranscript::query()->sole();

    $this->actingAs($admin)
        ->get("/admin/transcripts/{$transcript->id}")
        ->assertOk()
        ->assertSee($student->name)
        ->assertSee($transcript->transcript_number)
        ->assertSee('Икономика на труда')
        ->assertSee('6.00')
        ->assertSee(__('transcripts.print'));
});

it('blocks non-administrators from transcripts', function () {
    $student = User::factory()->student()->create();
    $teacher = User::factory()->teacher()->create();
    $hod = User::factory()->headOfDepartment()->create();
    $transcript = AcademicTranscript::factory()->create(['student_id' => $student->id]);

    $this->actingAs($student)->get("/admin/students/{$student->id}/transcripts")->assertForbidden();
    $this->actingAs($teacher)->post("/admin/students/{$student->id}/transcripts")->assertForbidden();
    $this->actingAs($hod)->get("/admin/transcripts/{$transcript->id}")->assertForbidden();
});

it('rejects transcript generation for a non-student target', function () {
    $admin = User::factory()->administrator()->create();
    $teacher = User::factory()->teacher()->create();

    $this->actingAs($admin)
        ->post("/admin/students/{$teacher->id}/transcripts")
        ->assertForbidden();

    $this->assertDatabaseCount('academic_transcripts', 0);
});

it('redirects guests from transcripts to login', function () {
    $student = User::factory()->student()->create();

    $this->get("/admin/students/{$student->id}/transcripts")->assertRedirect('/login');
});
