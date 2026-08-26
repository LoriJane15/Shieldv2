<?php

namespace App\Http\Requests\Lswdo;

use Illuminate\Foundation\Http\FormRequest;

class AssignFeaProcessorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assignFeaProcessor', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return ['processor_id' => ['required', 'integer', 'exists:users,id']];
    }
}
