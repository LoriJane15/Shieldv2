<?php

namespace App\Http\Requests\DilgReviewer;

use App\Models\EclipRegionalDisbursementReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRegionalDisbursementReportRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (preg_match('/^\d{4}-\d{2}$/', (string) $this->input('reporting_month'))) {
            $this->merge(['reporting_month' => $this->input('reporting_month').'-01']);
        }
    }

    public function authorize(): bool
    {
        $case = $this->route('eclipCase') ?? $this->route('disbursementReport')?->eclipCase;

        return $case
            && $this->user()?->hasRole('dilg_regional')
            && $this->user()->can('viewWorkflow', $case);
    }

    public function rules(): array
    {
        return [
            'reporting_month' => ['required', 'date'],
            'form_11_reference' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(EclipRegionalDisbursementReport::STATUSES)],
            'submitted_at' => ['nullable', 'date'],
            'returned_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'returned')],
            'return_reason' => ['nullable', 'string', 'max:5000', Rule::requiredIf($this->input('status') === 'returned')],
            'resubmitted_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'resubmitted')],
            'accepted_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'accepted')],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
