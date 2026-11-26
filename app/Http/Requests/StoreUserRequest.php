<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Roles that require a department.
     *
     * @var array<int, string>
     */
    private const DEPARTMENT_ROLES = ['teacher', 'head_of_department'];

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
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
            'department' => [
                Rule::requiredIf(fn (): bool => $this->needsDepartment()),
                'nullable',
                'string',
                'max:120',
            ],
            'teacher_code' => ['nullable', 'string', 'max:50', 'unique:users,teacher_code'],
            'student_code' => ['nullable', 'string', 'max:50', 'unique:users,student_code'],
        ];
    }

    /**
     * Determine whether the selected roles require a department.
     */
    public function needsDepartment(): bool
    {
        return collect($this->input('roles', []))
            ->intersect(self::DEPARTMENT_ROLES)
            ->isNotEmpty();
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'roles' => __('common.roles'),
            'department' => __('common.department'),
        ];
    }
}
