<?php

namespace App\Http\Requests\Lswdo;

use Illuminate\Foundation\Http\FormRequest;

class RequestAuthenticationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('lswdo')
            && $this->route('eclipCase')?->hasActiveParticipant($this->user());
    }

    public function rules(): array
    {
        return ['assigned_to' => ['required', 'integer', 'exists:users,id']];
    }
}
