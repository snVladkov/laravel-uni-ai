<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Models\AcademicTranscript;
use App\Models\Course;
use App\Models\User;

class DashboardSummary
{
    /**
     * Build the role-aware dashboard metrics for the user.
     *
     * @return array<int, array{label: string, value: string, hint: string|null, url: string|null, tone: string}>
     */
    public function for(User $user): array
    {
        return match (true) {
            $user->hasRole('administrator') => $this->administrator(),
            $user->hasRole('head_of_department') => $this->headOfDepartment($user),
            $user->hasRole('teacher') => $this->teacher($user),
            $user->hasRole('student') => $this->student($user),
            default => [],
        };
    }

    /**
     * Administrator metrics across the whole system.
     *
     * @return array<int, array{label: string, value: string, hint: string|null, url: string|null, tone: string}>
     */
    private function administrator(): array
    {
        return [
            $this->card(__('dashboard.metrics.users'), (string) User::query()->count(), __('dashboard.metrics.users_hint'), route('admin.users.index'), 'burgundy'),
            $this->card(__('dashboard.metrics.students'), (string) User::role('student')->count(), __('dashboard.metrics.students_hint'), route('admin.users.index', ['role' => 'student']), 'crimson'),
            $this->card(__('dashboard.metrics.courses'), (string) Course::query()->count(), __('dashboard.metrics.courses_hint'), route('courses.index'), 'gold'),
            $this->card(__('dashboard.metrics.transcripts'), (string) AcademicTranscript::query()->count(), __('dashboard.metrics.transcripts_hint'), null, 'slate'),
        ];
    }

    /**
     * Head of department metrics scoped to their department.
     *
     * @return array<int, array{label: string, value: string, hint: string|null, url: string|null, tone: string}>
     */
    private function headOfDepartment(User $user): array
    {
        return [
            $this->card(__('dashboard.metrics.pending'), (string) Course::query()->pendingReview($user->department)->count(), __('dashboard.metrics.pending_hint'), route('hod.courses.pending'), 'gold'),
            $this->card(__('dashboard.metrics.teachers'), (string) User::role('teacher')->where('department', $user->department)->count(), __('dashboard.metrics.teachers_hint'), route('hod.teachers.index'), 'burgundy'),
            $this->card(__('dashboard.metrics.department_courses'), (string) Course::query()->where('department', $user->department)->count(), __('dashboard.metrics.department_courses_hint'), route('courses.index'), 'crimson'),
        ];
    }

    /**
     * Teacher metrics for the courses they own.
     *
     * @return array<int, array{label: string, value: string, hint: string|null, url: string|null, tone: string}>
     */
    private function teacher(User $user): array
    {
        $courses = $user->createdCourses();

        return [
            $this->card(__('dashboard.metrics.my_courses'), (string) $courses->count(), __('dashboard.metrics.my_courses_hint'), route('courses.index'), 'burgundy'),
            $this->card(__('dashboard.metrics.drafts'), (string) $user->createdCourses()->where('status', CourseStatus::Draft)->count(), __('dashboard.metrics.drafts_hint'), route('courses.index', ['status' => CourseStatus::Draft->value]), 'slate'),
            $this->card(__('dashboard.metrics.awaiting_review'), (string) $user->createdCourses()->where('status', CourseStatus::PendingApproval)->count(), __('dashboard.metrics.awaiting_review_hint'), route('courses.index', ['status' => CourseStatus::PendingApproval->value]), 'gold'),
            $this->card(__('dashboard.metrics.enrolled_students'), (string) $user->createdCourses()->withCount('enrollments')->get()->sum('enrollments_count'), __('dashboard.metrics.enrolled_students_hint'), null, 'crimson'),
        ];
    }

    /**
     * Student metrics for their enrolments and grades.
     *
     * @return array<int, array{label: string, value: string, hint: string|null, url: string|null, tone: string}>
     */
    private function student(User $user): array
    {
        $graded = $user->enrollments()->whereNotNull('grade');
        $average = (clone $graded)->avg('grade');

        return [
            $this->card(__('dashboard.metrics.my_courses'), (string) $user->enrollments()->count(), __('dashboard.metrics.student_courses_hint'), route('courses.index'), 'burgundy'),
            $this->card(__('dashboard.metrics.graded_courses'), (string) $graded->count(), __('dashboard.metrics.graded_courses_hint'), route('courses.index'), 'crimson'),
            $this->card(__('dashboard.metrics.average'), $average !== null ? number_format((float) $average, 2) : '—', __('dashboard.metrics.average_hint'), null, 'gold'),
        ];
    }

    /**
     * Shape a single dashboard metric card.
     *
     * @return array{label: string, value: string, hint: string|null, url: string|null, tone: string}
     */
    private function card(string $label, string $value, ?string $hint, ?string $url, string $tone): array
    {
        return compact('label', 'value', 'hint', 'url', 'tone');
    }
}
