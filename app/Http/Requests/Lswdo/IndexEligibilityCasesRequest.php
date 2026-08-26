<?php

namespace App\Http\Requests\Lswdo;

use App\Enums\EclipCaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEligibilityCasesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('lswdo') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in($this->allowedStatuses())],
            'date' => ['nullable', Rule::in(['today', 'week', 'month'])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->query('search')) ?: null,
            'status' => $this->query('status') ?: null,
            'date' => $this->query('date') ?: null,
            'sort' => $this->query('sort') ?: 'newest',
        ]);
    }

    private function allowedStatuses(): array
    {
        return collect(EclipCaseStatus::cases())
            ->reject(fn (EclipCaseStatus $status) => $status === EclipCaseStatus::Draft)
            ->map(fn (EclipCaseStatus $status) => $status->value)
            ->all();
    }
}
