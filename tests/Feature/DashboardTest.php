<?php

use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    seedRoles();
});

it('renders the dashboard for every role', function (string $role) {
    $this->actingAs(userWithRole($role))
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(__('dashboard.quick_actions'));
})->with(['administrator', 'head_of_department', 'teacher', 'student']);

it('shows the administrator system metrics', function () {
    $admin = User::factory()->administrator()->create();
    User::factory()->student()->count(4)->create();
    Course::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(__('dashboard.metrics.users'))
        ->assertSee(__('dashboard.metrics.courses'))
        ->assertSee(__('dashboard.metrics.transcripts'));
});

it('shows the teacher own course metrics', function () {
    $teacher = User::factory()->teacher()->create();
    Course::factory()->forTeacher($teacher)->count(2)->create();

    $this->actingAs($teacher)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(__('dashboard.metrics.my_courses'))
        ->assertSee(__('dashboard.metrics.drafts'))
        ->assertSee(__('dashboard.metrics.enrolled_students'));
});

it('shows the student average grade', function () {
    $student = User::factory()->student()->create();
    $course = Course::factory()->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);
    $course->enrollments()->where('student_id', $student->id)->update([
        'grade' => 4.75,
        'grade_letter' => 'C',
        'graded_at' => now(),
    ]);

    $this->actingAs($student)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(__('dashboard.metrics.average'))
        ->assertSee('4.75');
});

it('shows pending approvals to the head of department', function () {
    $hod = User::factory()->headOfDepartment('Информатика')->create();
    Course::factory()->pendingApproval()->create(['department' => 'Информатика']);

    $this->actingAs($hod)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee(__('dashboard.metrics.pending'));
});
