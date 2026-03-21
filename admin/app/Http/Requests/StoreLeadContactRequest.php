<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'interest' => ['required', Rule::in(['process', 'software', 'cloud'])],
            'business_stage' => ['nullable', Rule::in(['ideation', 'validation', 'scale', 'operations'])],
            'message' => ['nullable', 'string', 'max:10000'],
            'consent_lgpd' => ['required', 'accepted'],
            'source_path' => ['nullable', 'string', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'consent_lgpd.accepted' => 'É necessário aceitar a política de privacidade.',
        ];
    }
}
