<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCourseMaterialRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip,txt,mp4', 'max:51200'],
            'external_url' => ['nullable', 'url', 'max:2048'],
        ];
    }

    /**
     * Ensure the material has either a file or an external link.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->hasFile('file') || filled($this->input('external_url'))) {
                    return;
                }

                $message = __('courses.materials.file_or_url_required');

                $validator->errors()->add('file', $message);
                $validator->errors()->add('external_url', $message);
            },
        ];
    }
}
