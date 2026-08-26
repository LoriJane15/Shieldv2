<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEclipWorkflowActivityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $activity = $this->route('activity');
        $definition = collect(config('eclip_workflow.steps', []))->firstWhere('code', $activity?->step_code) ?? [];
        $fields = $definition['fields'] ?? [];
        $allowedKeys = collect($fields)->pluck('key')->all();
        $data = array_intersect_key((array) $this->input('data', []), array_flip($allowedKeys));

        foreach ($fields as $field) {
            if (($field['type'] ?? null) === 'checkbox') {
                $data[$field['key']] = $this->boolean("data.{$field['key']}");
            }
        }

        $this->merge(['data' => $data]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('updateWorkflowActivity', $this->route('activity')) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'status' => ['required', Rule::in(['ongoing', 'completed', 'not_applicable', 'returned_for_correction'])],
            'remarks' => ['nullable', 'string', 'max:5000', 'required_if:status,returned_for_correction'],
            'data' => ['nullable', 'array'],
        ];

        $definition = collect(config('eclip_workflow.steps', []))
            ->firstWhere('code', $this->route('activity')?->step_code) ?? [];
        foreach ($definition['fields'] ?? [] as $field) {
            $rules["data.{$field['key']}"] = match ($field['type'] ?? 'text') {
                'checkbox' => ['nullable', 'boolean'],
                'date' => ['nullable', 'date'],
                'datetime-local' => ['nullable', 'date'],
                'month' => ['nullable', 'date_format:Y-m'],
                'number' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
                'select' => ['nullable', Rule::in(array_keys($field['options'] ?? []))],
                default => ['nullable', 'string', 'max:5000'],
            };

            if (($field['required_on_complete'] ?? false) && $this->input('status') === 'completed') {
                array_unshift($rules["data.{$field['key']}"], 'required');
            }
        }

        return $rules;
    }
}
