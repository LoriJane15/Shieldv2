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
            'decision' => ['required', Rule::in(['eligible', 'ineligible', 'returned'])],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_if:decision,ineligible,returned'],
        ];
    }
}
