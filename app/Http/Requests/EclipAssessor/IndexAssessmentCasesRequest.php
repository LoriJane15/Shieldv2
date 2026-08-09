<?php

namespace App\Http\Requests\EclipAssessor;

use App\Enums\EclipCaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAssessmentCasesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('lswdo', 'eclip_assessor') ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in($this->allowedStatuses())],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->query('search')) ?: null,
            'status' => $this->query('status') ?: null,
            'sort' => $this->query('sort') ?: 'newest',
        ]);
    }

    private function allowedStatuses(): array
    {
        return [
            EclipCaseStatus::DocumentsCertified->value,
            EclipCaseStatus::AssistanceAssessment->value,
            EclipCaseStatus::SubmittedForDilgReview->value,
            EclipCaseStatus::ReturnedForAssessmentRevision->value,
            EclipCaseStatus::Approved->value,
            EclipCaseStatus::Rejected->value,
        ];
    }
}
