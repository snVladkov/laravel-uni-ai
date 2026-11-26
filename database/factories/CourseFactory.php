<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'code' => mb_strtoupper(fake()->unique()->bothify('???###')),
            'description' => fake()->paragraph(),
            'status' => CourseStatus::Draft,
            'rejection_reason' => null,
            'department' => fake()->randomElement(['Информатика', 'Финанси', 'Маркетинг']),
            'teacher_id' => User::factory(),
            'approved_by' => null,
            'approved_at' => null,
        ];
    }

    public function forTeacher(User $teacher): static
    {
        return $this->state(fn () => [
            'teacher_id' => $teacher->id,
            'department' => $teacher->department,
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::PendingApproval]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Approved,
            'approved_at' => now(),
        ]);
    }

    public function rejected(string $reason = 'Необходими са корекции.'): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Rejected,
            'rejection_reason' => $reason,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => CourseStatus::Completed,
            'approved_at' => now(),
        ]);
    }
}
