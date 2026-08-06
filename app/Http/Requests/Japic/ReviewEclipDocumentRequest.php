<?php

namespace App\Http\Requests\Japic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewEclipDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reviewDocument', $this->route('document')->eclipCase) ?? false;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['authenticated', 'certified', 'invalid'])],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_if:decision,invalid'],
        ];
    }
}
