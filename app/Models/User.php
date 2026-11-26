<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'student_code', 'teacher_code', 'department'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Courses created by this teacher.
     *
     * @return HasMany<Course, $this>
     */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    /**
     * Materials uploaded by this user.
     *
     * @return HasMany<CourseMaterial, $this>
     */
    public function uploadedMaterials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class, 'uploaded_by');
    }

    /**
     * Courses the user is enrolled in as a student.
     *
     * @return BelongsToMany<Course, $this>
     */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_enrollments', 'student_id', 'course_id')
            ->withPivot(['grade', 'grade_letter', 'graded_by', 'graded_at', 'enrolled_at', 'enrolled_by'])
            ->withTimestamps();
    }

    /**
     * Enrolment records for this student.
     *
     * @return HasMany<CourseEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'student_id');
    }

    /**
     * Academic transcripts generated for this student.
     *
     * @return HasMany<AcademicTranscript, $this>
     */
    public function transcripts(): HasMany
    {
        return $this->hasMany(AcademicTranscript::class, 'student_id');
    }
}
