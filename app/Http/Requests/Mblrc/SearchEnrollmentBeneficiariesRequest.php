<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Foundation\Http\FormRequest;

class SearchEnrollmentBeneficiariesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mblrc') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['search' => trim((string) $this->input('search'))]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'required_without:beneficiary_id', 'string', 'min:2', 'max:100'],
            'beneficiary_id' => ['nullable', 'required_without:search', 'integer', 'exists:former_rebels,id'],
        ];
    }
}
