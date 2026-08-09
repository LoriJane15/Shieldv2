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
}
