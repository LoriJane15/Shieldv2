<?php

namespace App\Http\Requests\EclipAssessor;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assessAssistance', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:eclip_assistance_categories,id'],
            'requested_amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:9999999999999.99'],
            'assessed_amount' => ['nullable', 'numeric', 'gte:0', 'decimal:0,2', 'max:9999999999999.99'],
            'justification' => ['required', 'string', 'max:10000'],
            'assessment_remarks' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
