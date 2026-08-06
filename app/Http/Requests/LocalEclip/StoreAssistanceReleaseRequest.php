<?php

namespace App\Http\Requests\LocalEclip;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssistanceReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('releaseAssistance', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:9999999999999.99'],
            'release_reference' => ['required', 'string', 'max:150', Rule::unique('eclip_assistance_releases', 'release_reference')],
            'released_at' => ['required', 'date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'acknowledgment' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['release_reference' => trim((string) $this->release_reference)]);
    }
}
