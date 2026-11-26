<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TeacherManagementController extends Controller
{
    /**
     * List the teachers of the head of department's department.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage-teachers');

        $teachers = User::query()
            ->role('teacher')
            ->where('department', $request->user()->department)
            ->orderBy('name')
            ->paginate(15);

        return view('hod.teachers.index', compact('teachers'));
    }

    /**
     * Show the form for creating a teacher.
     */
    public function create(Request $request): View
    {
        Gate::authorize('manage-teachers');

        return view('hod.teachers.create', [
            'teacher' => new User,
            'department' => $request->user()->department,
        ]);
    }

    /**
     * Store a new teacher in the head of department's department.
     */
    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        Gate::authorize('manage-teachers');

        $teacher = DB::transaction(function () use ($request): User {
            $teacher = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'teacher_code' => $request->validated('teacher_code'),
                'department' => $request->user()->department,
            ]);

            $teacher->assignRole('teacher');

            return $teacher;
        });

        return redirect()
            ->route('hod.teachers.index')
            ->with('success', __('teachers.flash.created', ['name' => $teacher->name]));
    }

    /**
     * Show the form for editing the teacher.
     */
    public function edit(Request $request, User $teacher): View
    {
        Gate::authorize('manage-teacher', $teacher);

        return view('hod.teachers.edit', [
            'teacher' => $teacher,
            'department' => $teacher->department,
        ]);
    }

    /**
     * Update the teacher's details.
     */
    public function update(UpdateTeacherRequest $request, User $teacher): RedirectResponse
    {
        Gate::authorize('manage-teacher', $teacher);

        $attributes = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'teacher_code' => $request->validated('teacher_code'),
        ];

        if (filled($request->validated('password'))) {
            $attributes['password'] = $request->validated('password');
        }

        $teacher->update($attributes);

        return redirect()
            ->route('hod.teachers.index')
            ->with('success', __('teachers.flash.updated', ['name' => $teacher->name]));
    }

    /**
     * Delete the teacher, their courses and stored course material files.
     */
    public function destroy(Request $request, User $teacher): RedirectResponse
    {
        Gate::authorize('manage-teacher', $teacher);

        DB::transaction(function () use ($teacher): void {
            CourseMaterial::query()
                ->whereIn('course_id', $teacher->createdCourses()->select('id'))
                ->whereNotNull('file_path')
                ->pluck('file_path')
                ->each(fn (string $path) => Storage::disk('local')->delete($path));

            $teacher->delete();
        });

        return redirect()
            ->route('hod.teachers.index')
            ->with('success', __('teachers.flash.deleted', ['name' => $teacher->name]));
    }
}
