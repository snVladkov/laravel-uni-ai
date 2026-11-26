<?php

namespace App\Policies;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine whether the user can view any courses.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the course.
     */
    public function view(User $user, Course $course): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        if ($user->hasRole('head_of_department')) {
            return $course->department === $user->department;
        }

        if ($course->teacher_id === $user->id) {
            return true;
        }

        return $user->hasRole('student')
            && $course->status->isVisibleToStudents()
            && $course->enrollments()->where('student_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create a course.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('teacher')
            && $user->can('courses.create')
            && filled($user->department);
    }

    /**
     * Determine whether the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $course->teacher_id === $user->id
            && $user->can('courses.update.own')
            && $course->status->isEditable();
    }

    /**
     * Determine whether the user can submit the course for approval.
     */
    public function submitForApproval(User $user, Course $course): bool
    {
        return $course->teacher_id === $user->id
            && $user->can('courses.submit')
            && $course->status->isEditable();
    }

    /**
     * Determine whether the user can approve the course.
     */
    public function approve(User $user, Course $course): bool
    {
        return $this->reviewsDepartment($user, $course)
            && $course->status === CourseStatus::PendingApproval;
    }

    /**
     * Determine whether the user can reject the course.
     */
    public function reject(User $user, Course $course): bool
    {
        return $this->approve($user, $course);
    }

    /**
     * Determine whether the user can mark the course as completed.
     */
    public function markCompleted(User $user, Course $course): bool
    {
        return $this->reviewsDepartment($user, $course)
            && $course->status === CourseStatus::Approved;
    }

    /**
     * Determine whether the user can see the full student roster.
     */
    public function viewRoster(User $user, Course $course): bool
    {
        if ($user->hasRole('administrator')) {
            return true;
        }

        if ($user->hasRole('head_of_department')) {
            return $course->department === $user->department;
        }

        return $course->teacher_id === $user->id;
    }

    /**
     * Determine whether the user can enroll students in the course.
     */
    public function enroll(User $user, Course $course): bool
    {
        return $user->hasRole('administrator')
            && $user->can('courses.enroll_students')
            && $course->status->isApprovedOrCompleted();
    }

    /**
     * Determine whether the user can remove a student from the course.
     */
    public function removeStudent(User $user, Course $course): bool
    {
        return $user->hasRole('administrator')
            && $user->can('courses.enroll_students');
    }

    /**
     * Determine whether the user can grade students in the course.
     */
    public function grade(User $user, Course $course): bool
    {
        return $course->teacher_id === $user->id
            && $user->can('courses.grade.own')
            && $course->status->isApprovedOrCompleted();
    }

    /**
     * Check that the user reviews the course's department.
     */
    private function reviewsDepartment(User $user, Course $course): bool
    {
        return $user->hasRole('head_of_department')
            && $user->can('courses.review')
            && $course->department === $user->department;
    }
}
