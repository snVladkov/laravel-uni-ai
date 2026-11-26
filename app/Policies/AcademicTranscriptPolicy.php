<?php

namespace App\Policies;

use App\Models\AcademicTranscript;
use App\Models\User;

class AcademicTranscriptPolicy
{
    /**
     * Determine whether the user can generate a transcript for the student.
     */
    public function create(User $user, User $student): bool
    {
        return $this->isAdministrator($user)
            && $student->hasRole('student');
    }

    /**
     * Determine whether the user can view the transcript.
     */
    public function view(User $user, AcademicTranscript $transcript): bool
    {
        return $this->isAdministrator($user);
    }

    /**
     * Check that the user is an administrator with transcript access.
     */
    private function isAdministrator(User $user): bool
    {
        return $user->hasRole('administrator')
            && $user->can('transcripts.view');
    }
}
