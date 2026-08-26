<?php

namespace App\Http\Requests\Lswdo;

use App\Models\EclipLivelihoodBeneficiaryAssistance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipLivelihoodBeneficiaryAssistanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $case = $this->route('eclipCase') ?? $this->route('livelihoodAssistance')?->eclipCase;

        return $this->user()?->hasRole('lswdo')
            && $case?->hasActiveParticipant($this->user(), 'case_processor');
    }

    public function rules(): array
    {
        return [
            'implementation_reason' => ['required', 'string', 'max:5000'],
            'beneficiary_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:255'],
            'approval_reference' => ['required', 'string', 'max:255'],
            'approval_status' => ['required', Rule::in(EclipLivelihoodBeneficiaryAssistance::APPROVAL_STATUSES)],
            'assistance_amount' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'release_status' => ['required', Rule::in(EclipLivelihoodBeneficiaryAssistance::RELEASE_STATUSES)],
            'release_date' => ['nullable', 'required_if:release_status,released', 'date', 'before_or_equal:today'],
            'supporting_reference' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
