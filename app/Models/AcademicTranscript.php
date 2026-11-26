<?php

namespace App\Models;

use Database\Factories\AcademicTranscriptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transcript_number', 'student_id', 'generated_by', 'gpa', 'snapshot_data', 'generated_at'])]
class AcademicTranscript extends Model
{
    /** @use HasFactory<AcademicTranscriptFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'snapshot_data' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    /**
     * The student the transcript belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * The administrator who generated the transcript.
     *
     * @return BelongsTo<User, $this>
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
