<?php

namespace App\Http\Requests\LocalEclip;

use App\Enums\EclipCaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSurfacedFormerRebelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('local_eclip_committee')
            && $this->user()->municipality_id !== null;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'case_status' => ['nullable', Rule::in(collect(EclipCaseStatus::cases())->map->value->all())],
        ];
    }
}
