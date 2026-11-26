<?php

namespace App\Http\Requests;

use App\Rules\GradeStep;
use Illuminate\Foundation\Http\FormRequest;

class GradeStudentRequest extends FormRequest
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
        return [
            'grade' => ['required', 'numeric', 'between:2,6', new GradeStep],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'grade' => __('common.grade'),
        ];
    }
}
