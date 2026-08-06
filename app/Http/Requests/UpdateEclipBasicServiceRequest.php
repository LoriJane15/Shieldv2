<?php

namespace App\Http\Requests;

use App\Models\EclipBasicService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEclipBasicServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('basicService')) ?? false;
    }

    public function rules(): array
    {
        $lswdo = $this->user()->hasRole('lswdo');

        return [
            'gov_agency_id' => [$lswdo ? 'nullable' : 'prohibited', 'exists:gov_agencies,id'],
            'status' => ['required', Rule::in(EclipBasicService::STATUSES)],
            'referral_date' => [$lswdo ? 'nullable' : 'prohibited', 'date'],
            'target_completion_date' => [$lswdo ? 'nullable' : 'prohibited', 'date', 'after_or_equal:referral_date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
