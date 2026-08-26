<?php

namespace App\Http\Requests\Lswdo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestAuthenticationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('lswdo')
            && $this->route('eclipCase')?->hasActiveParticipant($this->user());
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'japic')
                    ->where('is_active', true)),
            ],
        ];
    }
}
