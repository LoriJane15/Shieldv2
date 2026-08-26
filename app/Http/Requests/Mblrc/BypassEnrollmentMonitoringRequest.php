<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Foundation\Http\FormRequest;

class BypassEnrollmentMonitoringRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');

        return app()->environment(['local', 'testing'])
            && $this->user()?->hasRole('mblrc')
            && $enrollment?->assigned_user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [];
    }
}
