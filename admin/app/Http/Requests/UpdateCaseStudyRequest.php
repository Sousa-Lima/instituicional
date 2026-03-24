<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCaseStudyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
            'featured' => ['sometimes', 'boolean'],
            'title' => ['sometimes', 'string', 'max:255'],
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'sector' => ['sometimes', 'string', 'max:255'],
            'short_summary' => ['sometimes', 'string', 'max:10000'],
            'content_html' => ['sometimes', 'string'],
            'metrics' => ['sometimes', 'array'],
            'main_image' => ['sometimes', 'array'],
            'seo' => ['sometimes', 'array'],
        ];
    }
}
