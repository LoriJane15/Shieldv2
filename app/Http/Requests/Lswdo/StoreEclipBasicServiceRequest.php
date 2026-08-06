<?php

namespace App\Http\Requests\Lswdo;

use App\Models\EclipBasicService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipBasicServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $case = $this->route('eclipCase');

        return $this->user()?->hasRole('lswdo')
            && $this->user()->municipality_id === $case?->municipality_id;
    }

    public function rules(): array
    {
        return [
            'service_type' => ['required', Rule::in(array_keys(config('shield.eclip_basic_service_types', []))), Rule::unique('eclip_basic_services')->where('eclip_case_id', $this->route('eclipCase')->id)],
            'gov_agency_id' => ['nullable', 'exists:gov_agencies,id'],
            'status' => ['required', Rule::in(EclipBasicService::STATUSES)],
            'referral_date' => ['nullable', 'date'],
            'target_completion_date' => ['nullable', 'date', 'after_or_equal:referral_date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
