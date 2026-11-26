<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display the courses visible to the current user.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $courses = Course::query()
            ->visibleTo($request->user())
            ->with('teacher')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%");
            }))
            ->when(
                in_array($status, array_column(CourseStatus::cases(), 'value'), true),
                fn ($query) => $query->where('status', $status),
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('courses.index', compact('courses', 'search', 'status'));
    }

    /**
     * Show the form for creating a course.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Course::class);

        return view('courses.create', [
            'course' => new Course,
            'department' => $request->user()->department,
        ]);
    }

    /**
     * Store a newly created course.
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $course = Course::create([
            ...$request->validated(),
            'teacher_id' => $request->user()->id,
            'department' => $request->user()->department,
            'status' => $request->string('action')->toString() === 'submit'
                ? CourseStatus::PendingApproval
                : CourseStatus::Draft,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.created'));
    }

    /**
     * Display the course.
     */
    public function show(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $course->load(['teacher', 'approver', 'materials.uploader', 'enrollments.student', 'enrollments.gradedBy']);

        $ownEnrollment = $request->user()->hasRole('student')
            ? $course->enrollments->firstWhere('student_id', $request->user()->id)
            : null;

        $availableStudents = $request->user()->can('enroll', $course)
            ? User::query()
                ->role('student')
                ->whereNotIn('id', $course->enrollments->pluck('student_id'))
                ->orderBy('name')
                ->get()
            : collect();

        return view('courses.show', compact('course', 'ownEnrollment', 'availableStudents'));
    }

    /**
     * Show the form for editing a course.
     */
    public function edit(Request $request, Course $course): View
    {
        $this->authorize('update', $course);

        return view('courses.edit', [
            'course' => $course,
            'department' => $course->department,
        ]);
    }

    /**
     * Update the course.
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $course->update($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.updated'));
    }

    /**
     * Submit the course for head of department approval.
     */
    public function submitForApproval(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('submitForApproval', $course);

        $course->update([
            'status' => CourseStatus::PendingApproval,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.submitted'));
    }
}
