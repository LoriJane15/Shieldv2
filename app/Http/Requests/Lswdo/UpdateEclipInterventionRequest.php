<?php

namespace App\Http\Requests\Lswdo;

class UpdateEclipInterventionRequest extends StoreEclipInterventionRequest
{
    public function authorize(): bool
    {
        $intervention = $this->route('eclipIntervention');

        return $this->user()?->hasRole('lswdo')
            && $intervention?->eclipCase?->hasActiveParticipant($this->user(), 'case_processor');
    }
}
