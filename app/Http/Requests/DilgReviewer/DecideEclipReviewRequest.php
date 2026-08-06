<?php

namespace App\Http\Requests\DilgReviewer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideEclipReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reviewForDilg', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['endorsed', 'approved', 'returned', 'rejected'])],
            'feedback' => ['nullable', 'string', 'max:10000', 'required_if:decision,returned,rejected'],
        ];
    }
}
