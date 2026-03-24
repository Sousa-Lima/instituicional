<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:255'],
            'short_description' => ['sometimes', 'string', 'max:5000'],
            'content_html' => ['sometimes', 'string'],
            'icon_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'category' => ['sometimes', Rule::in(['consultoria', 'software', 'cloud'])],
            'seo' => ['sometimes', 'array'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['draft', 'published'])],
        ];
    }
}
