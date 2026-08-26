<?php

namespace App\Http\Requests\Lswdo;

use App\Models\EclipIntervention;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipInterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $case = $this->route('eclipCase');

        return $this->user()?->hasRole('lswdo') && $case?->hasActiveParticipant($this->user(), 'case_processor');
    }

    public function rules(): array
    {
        $case = $this->route('eclipCase') ?? $this->route('eclipIntervention')?->eclipCase;

        return [
            'stage' => ['required', Rule::in(EclipIntervention::STAGES)],
            'reintegration_plan_item_id' => [
                'nullable',
                'required_if:stage,reintegration',
                Rule::exists('eclip_reintegration_plan_items', 'id')->where(fn ($query) => $query->where('eclip_case_id', $case?->id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'required_if:stage,reintegration', 'string', 'max:255'],
            'referral_date' => ['nullable', 'required_if:stage,reintegration', 'date'],
            'status' => ['required', Rule::in(EclipIntervention::STATUSES)],
            'amount_or_value' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
            'outcome' => ['nullable', 'string', 'max:5000', 'required_if:status,completed'],
            'evidence_reference' => ['nullable', 'string', 'max:255'],
            'receiving_agency' => ['nullable', 'required_if:status,transferred', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_if:status,returned,not_applicable'],
        ];
    }
}
