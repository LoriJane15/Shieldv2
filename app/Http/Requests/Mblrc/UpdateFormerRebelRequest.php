<?php

namespace App\Http\Requests\Mblrc;

/**
 * Same field rules as creation; separated so future edit-only constraints
 * (e.g. immutable classified_id) live here.
 */
class UpdateFormerRebelRequest extends StoreFormerRebelRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        foreach (['birthdate', 'contact_num', 'residential_address', 'surrender_date'] as $field) {
            $rules[$field][0] = 'nullable';
        }

        $rules['age'] = ['nullable', 'integer', 'min:0', 'max:120'];

        return $rules;
    }
}
