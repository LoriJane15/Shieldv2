<?php

namespace App\Http\Requests\Mblrc;

use App\Models\EclipCase;
use Illuminate\Foundation\Http\FormRequest;

class StoreEclipCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EclipCase::class) ?? false;
    }

    public function rules(): array
    {
        return ['former_rebel_id' => ['required', 'integer', 'exists:former_rebels,id']];
    }
}
