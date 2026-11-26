<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'code', 'description', 'status', 'rejection_reason', 'department', 'teacher_id', 'approved_by', 'approved_at'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /**
     * The teacher who created the course.
     *
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * The head of department who reviewed the course.
     *
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Materials uploaded to the course.
     *
     * @return HasMany<CourseMaterial, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class)->orderBy('order')->orderBy('id');
    }

    /**
     * Enrolments for the course.
     *
     * @return HasMany<CourseEnrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Students enrolled in the course.
     *
     * @return BelongsToMany<User, $this>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_enrollments', 'course_id', 'student_id')
            ->withPivot(['grade', 'grade_letter', 'graded_by', 'graded_at', 'enrolled_at', 'enrolled_by'])
            ->withTimestamps();
    }

    /**
     * Limit the query to courses the given user may see.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole('administrator')) {
            return $query;
        }

        if ($user->hasRole('head_of_department')) {
            return $query->where('department', $user->department);
        }

        if ($user->hasRole('teacher')) {
            return $query->where('teacher_id', $user->id);
        }

        if ($user->hasRole('student')) {
            return $query
                ->whereIn('status', [CourseStatus::Approved, CourseStatus::Completed])
                ->whereHas('enrollments', fn (Builder $enrollment) => $enrollment->where('student_id', $user->id));
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Limit the query to pending courses within a department.
     *
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopePendingReview(Builder $query, ?string $department = null): Builder
    {
        return $query
            ->where('status', CourseStatus::PendingApproval)
            ->when($department !== null, fn (Builder $query) => $query->where('department', $department));
    }
}
