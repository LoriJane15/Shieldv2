<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Foundation\Http\FormRequest;

class CompleteEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');

        return $this->user()?->hasRole('mblrc')
            && $enrollment?->assigned_user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'integration_completed_at' => ['required', 'date', 'before_or_equal:today'],
            'verified_municipality_id' => ['required', 'integer', 'exists:municipalities,id'],
            'location_verification_remarks' => ['nullable', 'string', 'max:5000'],
            'phase_one_evidence' => ['required', 'array'],
            'phase_one_evidence.intention_to_surface.source' => ['required', 'string', 'max:100'],
            'phase_one_evidence.intention_to_surface.source_record' => ['required', 'string', 'max:255'],
            'phase_one_evidence.receiving_unit_coordination.source' => ['required', 'string', 'max:100'],
            'phase_one_evidence.receiving_unit_coordination.source_record' => ['required', 'string', 'max:255'],
        ];
    }
}
