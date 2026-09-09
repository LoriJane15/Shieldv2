<?php

namespace App\Http\Requests\Japic;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class SaveCertificationDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('saveDraft', $this->route('japicCertificationProcessing')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['control_number' => is_string($this->control_number) ? trim($this->control_number) : $this->control_number,
            'delay_reason' => is_string($this->delay_reason) ? trim($this->delay_reason) : $this->delay_reason]);
    }

    public function rules(): array
    {
        $rules = [
            'revision' => ['required', 'integer', 'min:0'], 'lock_version' => ['required', 'integer', 'min:0'],
            'control_number' => ['required', 'string', 'max:100'], 'delay_reason' => ['nullable', 'string', 'max:2000'],
            'certificate' => ['nullable', 'array:date_issued,surrendering_unit,surrender_date,surrender_location,operating_area_supplement'],
            'certificate.date_issued' => ['nullable', 'date_format:Y-m-d'], 'certificate.surrendering_unit' => ['nullable', 'string', 'max:255'],
            'certificate.surrender_date' => ['nullable', 'date_format:Y-m-d'], 'certificate.surrender_location' => ['nullable', 'string', 'max:1000'],
            'certificate.operating_area_supplement' => ['nullable', 'string', 'max:4000'],
            'signatories' => ['nullable', 'array:provincial_afp,provincial_pnp,area_afp,area_pnp'],
        ];
        foreach (['provincial_afp', 'provincial_pnp', 'area_afp', 'area_pnp'] as $key) {
            $rules["signatories.{$key}"] = ['nullable', 'array:rank,name,suffix'];
            $rules["signatories.{$key}.rank"] = ['nullable', 'string', 'max:100'];
            $rules["signatories.{$key}.name"] = ['nullable', 'string', 'max:255'];
            $rules["signatories.{$key}.suffix"] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $allowed = ['_token', '_method', 'revision', 'lock_version', 'control_number', 'delay_reason', 'certificate', 'signatories'];
            if (array_diff(array_keys($this->all()), $allowed) !== []) {
                $validator->errors()->add('request', 'The request contains unsupported or server-owned fields.');
            }
        }];
    }

    public function manualPayload(): array
    {
        return ['certificate' => $this->validated('certificate', []), 'signatories' => $this->validated('signatories', [])];
    }
}
