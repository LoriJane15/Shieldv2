<?php

namespace App\Http\Requests\DilgReviewer;

use App\Models\EclipLiquidationRequirement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLiquidationRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $case = $this->route('eclipCase') ?? $this->route('liquidationRequirement')?->eclipCase;

        return $case
            && $this->user()?->hasRole('dilg_provincial_focal')
            && $this->user()->municipality_id === $case->municipality_id
            && $this->user()->can('viewWorkflow', $case);
    }

    public function rules(): array
    {
        return [
            'assistance_category' => ['required', 'string', 'max:255'],
            'requirement_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(EclipLiquidationRequirement::STATUSES)],
            'reference' => ['nullable', 'string', 'max:255'],
            'submitted_at' => ['nullable', 'date'],
            'returned_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'returned')],
            'return_reason' => ['nullable', 'string', 'max:5000', Rule::requiredIf($this->input('status') === 'returned')],
            'resubmitted_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'resubmitted')],
            'accepted_at' => ['nullable', 'date', Rule::requiredIf($this->input('status') === 'accepted')],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
