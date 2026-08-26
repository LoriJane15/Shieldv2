<?php

namespace App\Http\Requests\Lswdo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reviewEligibility', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['eligible', 'previously_assisted', 'for_clarification', 'not_eligible'])],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_unless:decision,eligible'],
            'referral_status' => ['nullable', Rule::requiredIf($this->input('decision') === 'not_eligible'), Rule::in(['not_referred', 'referred'])],
            'referred_program' => ['nullable', 'string', 'max:255', Rule::requiredIf($this->input('referral_status') === 'referred')],
        ];
    }
}
