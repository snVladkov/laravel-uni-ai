<?php

namespace App\Policies;

use App\Models\User;

class TeacherPolicy
{
    /**
     * Determine whether the user can manage teachers.
     */
    public function viewAny(User $user): bool
    {
        return $this->isDepartmentHead($user);
    }

    /**
     * Determine whether the user can create a teacher.
     */
    public function create(User $user): bool
    {
        return $this->isDepartmentHead($user);
    }

    /**
     * Determine whether the user can update the teacher.
     */
    public function update(User $user, User $teacher): bool
    {
        return $this->managesTeacher($user, $teacher);
    }

    /**
     * Determine whether the user can delete the teacher.
     */
    public function delete(User $user, User $teacher): bool
    {
        return $this->managesTeacher($user, $teacher);
    }

    /**
     * Check that the user is a head of department with the management permission.
     */
    private function isDepartmentHead(User $user): bool
    {
        return $user->hasRole('head_of_department')
            && $user->can('teachers.manage');
    }

    /**
     * Check that the teacher belongs to the head of department's department.
     */
    private function managesTeacher(User $user, User $teacher): bool
    {
        return $this->isDepartmentHead($user)
            && $teacher->hasRole('teacher')
            && $teacher->department === $user->department;
    }
}
