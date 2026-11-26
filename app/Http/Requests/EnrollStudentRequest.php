<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Course $course */
        $course = $this->route('course');

        return [
            'student_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
                Rule::unique('course_enrollments', 'student_id')->where('course_id', $course->id),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $student = User::query()->find($value);

                    if ($student === null || ! $student->hasRole('student')) {
                        $fail(__('validation.student_role'));
                    }
                },
            ],
        ];
    }
}
