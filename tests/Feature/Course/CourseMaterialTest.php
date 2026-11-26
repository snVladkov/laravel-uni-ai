<?php

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    seedRoles();
    Storage::fake('local');
});

it('lets the owning teacher upload a material file to the course private disk', function () {
    $teacher = User::factory()->teacher('Информатика')->create();
    $course = Course::factory()->forTeacher($teacher)->create();

    $file = UploadedFile::fake()->create('lecture-notes.pdf', 240, 'application/pdf');

    $this->actingAs($teacher)
        ->post("/courses/{$course->id}/materials", [
            'title' => 'Лекционни бележки',
            'description' => 'Първа тема',
            'file' => $file,
        ])
        ->assertRedirect();

    $material = CourseMaterial::query()->sole();

    expect($material->course_id)->toBe($course->id)
        ->and($material->uploaded_by)->toBe($teacher->id)
        ->and($material->title)->toBe('Лекционни бележки')
        ->and($material->file_path)->not->toBeNull()
        ->and($material->external_url)->toBeNull();

    Storage::disk('local')->assertExists($material->file_path);
    expect($material->file_path)->toStartWith("course-materials/{$course->id}/");
});

it('lets the owning teacher attach an external link instead of a file', function () {
    $teacher = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($teacher)->create();

    $this->actingAs($teacher)
        ->post("/courses/{$course->id}/materials", [
            'title' => 'Видео лекция',
            'external_url' => 'https://example.com/lecture',
        ])
        ->assertRedirect();

    $material = CourseMaterial::query()->sole();

    expect($material->external_url)->toBe('https://example.com/lecture')
        ->and($material->file_path)->toBeNull();
});

it('requires a file or an external url', function () {
    $teacher = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($teacher)->create();

    $this->actingAs($teacher)
        ->post("/courses/{$course->id}/materials", ['title' => 'Празен материал'])
        ->assertSessionHasErrors(['file', 'external_url']);

    $this->assertDatabaseCount('course_materials', 0);
});

it('rejects unsupported file types', function () {
    $teacher = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($teacher)->create();

    $this->actingAs($teacher)
        ->post("/courses/{$course->id}/materials", [
            'title' => 'Изпълним файл',
            'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])
        ->assertSessionHasErrors('file');
});

it('blocks a teacher from managing materials on another teacher course', function () {
    $owner = User::factory()->teacher()->create();
    $other = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($owner)->create();

    $this->actingAs($other)
        ->post("/courses/{$course->id}/materials", [
            'title' => 'Неоторизиран материал',
            'external_url' => 'https://example.com/x',
        ])
        ->assertForbidden();
});

it('allows enrolled students to download a material file', function () {
    $teacher = User::factory()->teacher()->create();
    $student = User::factory()->student()->create();
    $course = Course::factory()->forTeacher($teacher)->approved()->create();
    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    Storage::disk('local')->put("course-materials/{$course->id}/notes.pdf", 'contents');

    $material = CourseMaterial::factory()->create([
        'course_id' => $course->id,
        'uploaded_by' => $teacher->id,
        'file_path' => "course-materials/{$course->id}/notes.pdf",
        'external_url' => null,
    ]);

    $this->actingAs($student)
        ->get("/courses/{$course->id}/materials/{$material->id}/download")
        ->assertOk()
        ->assertDownload();
});

it('blocks outsiders and guests from downloading a material file', function () {
    $teacher = User::factory()->teacher()->create();
    $outsider = User::factory()->student()->create();
    $course = Course::factory()->forTeacher($teacher)->approved()->create();

    Storage::disk('local')->put("course-materials/{$course->id}/notes.pdf", 'contents');

    $material = CourseMaterial::factory()->create([
        'course_id' => $course->id,
        'uploaded_by' => $teacher->id,
        'file_path' => "course-materials/{$course->id}/notes.pdf",
        'external_url' => null,
    ]);

    $this->actingAs($outsider)
        ->get("/courses/{$course->id}/materials/{$material->id}/download")
        ->assertForbidden();
});

it('deletes a material and removes its stored file', function () {
    $teacher = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($teacher)->create();

    Storage::disk('local')->put("course-materials/{$course->id}/notes.pdf", 'contents');

    $material = CourseMaterial::factory()->create([
        'course_id' => $course->id,
        'uploaded_by' => $teacher->id,
        'file_path' => "course-materials/{$course->id}/notes.pdf",
        'external_url' => null,
    ]);

    $this->actingAs($teacher)
        ->delete("/courses/{$course->id}/materials/{$material->id}")
        ->assertRedirect();

    $this->assertModelMissing($material);
    Storage::disk('local')->assertMissing("course-materials/{$course->id}/notes.pdf");
});

it('blocks another teacher from deleting a material', function () {
    $owner = User::factory()->teacher()->create();
    $other = User::factory()->teacher()->create();
    $course = Course::factory()->forTeacher($owner)->create();
    $material = CourseMaterial::factory()->create([
        'course_id' => $course->id,
        'uploaded_by' => $owner->id,
        'external_url' => 'https://example.com/x',
        'file_path' => null,
    ]);

    $this->actingAs($other)
        ->delete("/courses/{$course->id}/materials/{$material->id}")
        ->assertForbidden();

    $this->assertModelExists($material);
});
