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
        return [
            'stage' => ['required', Rule::in(EclipIntervention::STAGES)],
            'title' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(EclipIntervention::STATUSES)],
            'amount_or_value' => ['nullable', 'numeric', 'min:0'],
            'target_date' => ['nullable', 'date'],
            'outcome' => ['nullable', 'string', 'max:5000', 'required_if:status,completed'],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_if:status,returned,not_applicable'],
        ];
    }
}
