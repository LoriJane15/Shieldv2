<?php

namespace App\Http\Requests\Mblrc;

use App\Models\MblrcEnrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEnrollmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mblrc') === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->filled('search') ? trim((string) $this->input('search')) : null,
            'sort' => $this->input('sort', 'recently_updated'),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([...MblrcEnrollment::STATUSES, 'attention'])],
            'sort' => ['required', Rule::in(['recently_updated', 'newest_started', 'oldest_started'])],
        ];
    }
}
