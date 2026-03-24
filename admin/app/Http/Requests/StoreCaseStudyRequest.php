<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaseStudyRequest extends FormRequest
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
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'featured' => ['nullable', 'boolean'],
            'title' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'sector' => ['required', 'string', 'max:255'],
            'short_summary' => ['required', 'string', 'max:10000'],
            'content_html' => ['required', 'string'],
            'metrics' => ['required', 'array'],
            'main_image' => ['required', 'array'],
            'seo' => ['required', 'array'],
        ];
    }
}
