<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mblrc') ?? false;
    }

    public function rules(): array
    {
        return [
            'former_rebel_id' => ['required', 'integer', 'exists:former_rebels,id'],
            'integration_started_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'former_rebel_id.required' => 'Select an FR/FVE beneficiary.',
            'former_rebel_id.exists' => 'The selected beneficiary is no longer available.',
            'integration_started_at.required' => 'Monitoring start date is required.',
            'integration_started_at.date' => 'Enter a valid monitoring start date.',
            'integration_started_at.before_or_equal' => 'Monitoring start date cannot be in the future.',
        ];
    }
}
