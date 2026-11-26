<?php

namespace App\Services;

use App\Models\AcademicTranscript;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class AcademicTranscriptService
{
    /**
     * Generate an immutable transcript snapshot for the student.
     */
    public function generate(User $student, User $generatedBy): AcademicTranscript
    {
        $snapshot = $this->buildSnapshot($student);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(fn (): AcademicTranscript => AcademicTranscript::create([
                    'transcript_number' => $this->nextNumber($attempt),
                    'student_id' => $student->id,
                    'generated_by' => $generatedBy->id,
                    'gpa' => $snapshot['average'],
                    'snapshot_data' => $snapshot,
                    'generated_at' => now(),
                ]));
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Unable to generate a unique transcript number.');
    }

    /**
     * Build the transcript snapshot from the student's graded courses.
     *
     * @return array<string, mixed>
     */
    public function buildSnapshot(User $student): array
    {
        $courses = $student->enrollments()
            ->with('course')
            ->whereNotNull('grade')
            ->get()
            ->map(fn ($enrollment): array => [
                'code' => $enrollment->course->code,
                'title' => $enrollment->course->title,
                'department' => $enrollment->course->department,
                'grade' => (float) $enrollment->grade,
                'grade_letter' => $enrollment->grade_letter,
                'graded_at' => $enrollment->graded_at?->toDateString(),
            ])
            ->values()
            ->all();

        $average = $courses === []
            ? null
            : round(array_sum(array_column($courses, 'grade')) / count($courses), 2);

        return [
            'student' => [
                'name' => $student->name,
                'email' => $student->email,
                'student_code' => $student->student_code,
            ],
            'courses' => $courses,
            'average' => $average,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build the next sequential transcript number for the current year.
     */
    private function nextNumber(int $offset = 0): string
    {
        $year = now()->year;

        $count = AcademicTranscript::query()
            ->where('transcript_number', 'like', "UEV-{$year}-%")
            ->count();

        return sprintf('UEV-%d-%04d', $year, $count + 1 + $offset);
    }
}
