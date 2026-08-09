<?php

namespace App\Http\Requests\Japic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideAuthenticationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('japic')
            && $this->route('authentication')?->assigned_to === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['authenticated', 'returned', 'not_authenticated'])],
            'certification_reference' => ['nullable', 'required_if:decision,authenticated', 'string', 'max:255'],
            'remarks' => ['nullable', 'required_if:decision,returned,not_authenticated', 'string', 'max:5000'],
        ];
    }
}
