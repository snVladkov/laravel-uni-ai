<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class CourseMaterialPolicy
{
    /**
     * Determine whether the user can add materials to the course.
     */
    public function create(User $user, Course $course): bool
    {
        return $this->managesCourse($user, $course)
            && $user->can('materials.manage.own');
    }

    /**
     * Determine whether the user can delete the material.
     */
    public function delete(User $user, CourseMaterial $material): bool
    {
        return $this->managesCourse($user, $material->course)
            && $user->can('materials.manage.own');
    }

    /**
     * Determine whether the user can view the material.
     */
    public function view(User $user, CourseMaterial $material): bool
    {
        return $user->can('materials.view')
            && Gate::forUser($user)->allows('view', $material->course);
    }

    /**
     * Determine whether the user can download the material file.
     */
    public function download(User $user, CourseMaterial $material): bool
    {
        return $this->view($user, $material);
    }

    /**
     * Check that the user owns the course.
     */
    private function managesCourse(User $user, Course $course): bool
    {
        return $course->teacher_id === $user->id;
    }
}
