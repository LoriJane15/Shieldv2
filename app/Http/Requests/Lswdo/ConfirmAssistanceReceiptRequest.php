<?php

namespace App\Http\Requests\Lswdo;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAssistanceReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        $release = $this->route('release');

        return $this->user()?->hasRole('lswdo')
            && $release?->eclipCase?->hasActiveParticipant($this->user(), 'case_processor');
    }

    public function rules(): array
    {
        return ['remarks' => ['nullable', 'string', 'max:5000']];
    }
}
