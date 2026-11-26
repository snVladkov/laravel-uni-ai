<?php

namespace App\Http\Controllers;

use App\Enums\GradeBand;
use App\Http\Requests\EnrollStudentRequest;
use App\Http\Requests\GradeStudentRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseEnrollmentController extends Controller
{
    /**
     * Enroll a student in the course.
     */
    public function store(EnrollStudentRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('enroll', $course);

        $course->enrollments()->create([
            'student_id' => $request->validated('student_id'),
            'enrolled_by' => $request->user()->id,
            'enrolled_at' => now(),
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.student_enrolled'));
    }

    /**
     * Remove a student's enrollment from the course.
     */
    public function destroy(Request $request, Course $course, User $student): RedirectResponse
    {
        $this->authorize('removeStudent', $course);

        $course->enrollments()->where('student_id', $student->id)->delete();

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.student_removed'));
    }

    /**
     * Record the final grade for an enrolled student.
     */
    public function grade(GradeStudentRequest $request, Course $course, User $student): RedirectResponse
    {
        $this->authorize('grade', $course);

        $enrollment = $course->enrollments()->where('student_id', $student->id)->firstOrFail();

        $score = (float) $request->validated('grade');

        $enrollment->update([
            'grade' => $score,
            'grade_letter' => GradeBand::fromScore($score)->letter(),
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.graded'));
    }
}
