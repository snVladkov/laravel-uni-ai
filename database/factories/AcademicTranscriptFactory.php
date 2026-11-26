<?php

namespace Database\Factories;

use App\Models\AcademicTranscript;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AcademicTranscript>
 */
class AcademicTranscriptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transcript_number' => 'UEV-'.now()->year.'-'.mb_strtoupper(Str::random(6)),
            'student_id' => User::factory()->student(),
            'generated_by' => User::factory()->administrator(),
            'gpa' => fake()->randomFloat(2, 3, 6),
            'snapshot_data' => [
                'student' => ['name' => fake()->name(), 'student_code' => fake()->numerify('S-####')],
                'courses' => [],
            ],
            'generated_at' => now(),
        ];
    }
}
