<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEclipWorkflowActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateWorkflowActivity', $this->route('activity')) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['ongoing', 'completed', 'not_applicable', 'returned_for_correction'])],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_if:status,returned_for_correction'],
            'data' => ['nullable', 'array'],
            'data.*' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
