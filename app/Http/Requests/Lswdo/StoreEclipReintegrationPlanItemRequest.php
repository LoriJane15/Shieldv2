<?php

namespace App\Http\Requests\Lswdo;

use App\Models\EclipReintegrationPlanItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipReintegrationPlanItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $case = $this->route('eclipCase') ?? $this->route('planItem')?->eclipCase;

        return $this->user()?->hasRole('lswdo')
            && $case?->hasActiveParticipant($this->user(), 'case_processor');
    }

    public function rules(): array
    {
        return [
            'identified_need' => ['required', 'string', 'max:5000'],
            'proposed_assistance' => ['required', 'string', 'max:255'],
            'responsible_agency' => ['required', 'string', 'max:255'],
            'lgu_counterpart' => ['nullable', 'string', 'max:255'],
            'form_of_assistance' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'target_date' => ['required', 'date'],
            'status' => ['required', Rule::in(EclipReintegrationPlanItem::STATUSES)],
            'partner_agencies' => ['nullable', 'string', 'max:5000'],
            'agency_commitments' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
