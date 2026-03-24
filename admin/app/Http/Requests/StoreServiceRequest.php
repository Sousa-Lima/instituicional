<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:5000'],
            'content_html' => ['required', 'string'],
            'icon_name' => ['nullable', 'string', 'max:100'],
            'category' => ['required', Rule::in(['consultoria', 'software', 'cloud'])],
            'seo' => ['required', 'array'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ];
    }
}
