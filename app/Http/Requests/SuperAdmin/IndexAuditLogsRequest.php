<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAuditLogsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'date_range' => $this->input('date_range', 'all'),
            'sort' => $this->input('sort', 'newest'),
            'per_page' => $this->input('per_page', 25),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:80', Rule::exists('audit_logs', 'action')],
            'module' => ['nullable', 'string', 'max:120', Rule::exists('audit_logs', 'entity_type')],
            'role' => ['nullable', 'string', 'max:50', Rule::in(array_keys(config('shield.roles', [])))],
            'date_range' => ['required', Rule::in(['all', 'today', 'last_7_days', 'last_30_days', 'this_month', 'custom'])],
            'date_from' => ['nullable', 'required_if:date_range,custom', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'required_if:date_range,custom', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'sort' => ['required', Rule::in(['newest', 'oldest'])],
            'per_page' => ['required', 'integer', Rule::in([25, 50, 100])],
        ];
    }
}
