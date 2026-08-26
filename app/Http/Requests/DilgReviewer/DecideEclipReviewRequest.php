<?php

namespace App\Http\Requests\DilgReviewer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideEclipReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reviewForDilg', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        $positiveDecision = $this->user()?->hasRole('dilg_provincial_focal', 'dilg_regional') ? 'endorsed' : 'approved';
        $isPositive = $this->input('decision') === $positiveDecision;
        $referenceField = match ($this->user()?->role) {
            'dilg_provincial_focal' => 'form_8_reference',
            'dilg_regional' => 'form_9_reference',
            default => 'form_10_reference',
        };

        return [
            'decision' => ['required', Rule::in(['endorsed', 'approved', 'returned', 'rejected'])],
            'feedback' => ['nullable', 'string', 'max:10000', 'required_if:decision,returned,rejected'],
            $referenceField => [Rule::requiredIf($isPositive), 'nullable', 'string', 'max:150'],
            'endorsement_date' => [Rule::requiredIf($isPositive), 'nullable', 'date', 'before_or_equal:today'],
            'eclip_is_reference' => ['nullable', 'string', 'max:150'],
        ];
    }
}
