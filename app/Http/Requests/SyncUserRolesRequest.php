<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncUserRolesRequest extends FormRequest
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
        ];
    }

    /**
     * Ensure the last administrator keeps the administrator role.
     *
     * @return array<int, Closure>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                if ($this->removesLastAdministrator()) {
                    $validator->errors()->add('roles', __('users.last_admin'));
                }
            },
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
     * Determine whether this request would strip the final administrator.
     */
    private function removesLastAdministrator(): bool
    {
        /** @var User $target */
        $target = $this->route('user');

        $stillAdministrator = in_array('administrator', (array) $this->input('roles', []), true);

        if (! $target->hasRole('administrator') || $stillAdministrator) {
            return false;
        }

        return User::role('administrator')->count() <= 1;
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
