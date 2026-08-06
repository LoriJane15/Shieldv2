<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipAssistanceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:80', 'regex:/^[A-Z0-9_]+$/', Rule::unique('eclip_assistance_categories', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->code))]);
    }
}
