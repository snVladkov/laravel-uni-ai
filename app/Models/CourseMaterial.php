<?php

namespace App\Models;

use Database\Factories\CourseMaterialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['course_id', 'uploaded_by', 'title', 'description', 'file_path', 'external_url', 'order'])]
class CourseMaterial extends Model
{
    /** @use HasFactory<CourseMaterialFactory> */
    use HasFactory;

    /**
     * The course the material belongs to.
     *
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The user who uploaded the material.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Remove the stored file when the material is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (CourseMaterial $material): void {
            if ($material->file_path !== null) {
                Storage::disk('local')->delete($material->file_path);
            }
        });
    }
}
