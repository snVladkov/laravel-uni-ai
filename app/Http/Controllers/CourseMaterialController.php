<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseMaterialRequest;
use App\Models\Course;
use App\Models\CourseMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseMaterialController extends Controller
{
    /**
     * Store a new material for the course.
     */
    public function store(StoreCourseMaterialRequest $request, Course $course): RedirectResponse
    {
        $this->authorize('create', [CourseMaterial::class, $course]);

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store("course-materials/{$course->id}", 'local');
        }

        $course->materials()->create([
            'uploaded_by' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'file_path' => $filePath,
            'external_url' => $request->validated('external_url'),
            'order' => (int) $course->materials()->max('order') + 1,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.material_uploaded'));
    }

    /**
     * Download the stored material file.
     */
    public function download(Request $request, Course $course, CourseMaterial $material): StreamedResponse
    {
        abort_unless($material->course_id === $course->id, 404);

        $this->authorize('download', $material);

        abort_if($material->file_path === null, 404);
        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download(
            $material->file_path,
            $material->title.'.'.pathinfo($material->file_path, PATHINFO_EXTENSION),
        );
    }

    /**
     * Delete the material and its stored file.
     */
    public function destroy(Request $request, Course $course, CourseMaterial $material): RedirectResponse
    {
        abort_unless($material->course_id === $course->id, 404);

        $this->authorize('delete', $material);

        $material->delete();

        return redirect()
            ->route('courses.show', $course)
            ->with('success', __('courses.flash.material_deleted'));
    }
}
