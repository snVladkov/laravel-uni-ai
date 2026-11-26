<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Http\Requests\RejectCourseRequest;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseApprovalController extends Controller
{
    /**
     * Display the pending approval queue for the head of department.
     */
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->pendingReview($request->user()->department)
            ->with('teacher')
            ->oldest()
            ->paginate(15);

        return view('hod.courses.pending', compact('courses'));
    }

    /**
     * Approve the given course.
     */
    public function approve(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('approve', $course);

        $course->update([
            'status' => CourseStatus::Approved,
            'rejection_reason' => null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('hod.courses.pending')
            ->with('success', __('courses.flash.approved'));
    }

    /**
     * Reject the given course with a reason.
     */
    public function reject(RejectCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('reject', $course);

        $course->update([
            'status' => CourseStatus::Rejected,
            'rejection_reason' => $request->validated('rejection_reason'),
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('hod.courses.pending')
            ->with('warning', __('courses.flash.rejected'));
    }

    /**
     * Mark the approved course as completed.
     */
    public function markCompleted(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('markCompleted', $course);

        $course->update(['status' => CourseStatus::Completed]);

        return redirect()
            ->back(fallback: route('courses.show', $course))
            ->with('success', __('courses.flash.completed'));
    }
}
