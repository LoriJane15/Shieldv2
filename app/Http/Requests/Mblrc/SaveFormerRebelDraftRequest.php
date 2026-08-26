<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Validation\Rule;

class SaveFormerRebelDraftRequest extends StoreFormerRebelRequest
{
    public function rules(): array
    {
        return [
            'firstname' => ['nullable', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III'])],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'civil_status' => ['nullable', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],
            'birthdate' => ['nullable', 'date'],
            'contact_num' => ['nullable', 'string', 'max:15'],
            'municipality_id' => ['nullable', 'exists:municipalities,id'],
            'barangay_id' => [
                'nullable',
                Rule::exists('barangays', 'id')
                    ->where('municipality_id', $this->input('municipality_id')),
            ],
            'province' => ['nullable', 'string', 'max:50'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'residential_address' => ['nullable', 'string', 'max:255'],
            'surrender_date' => ['nullable', 'date'],
            'surrender_reason' => ['nullable', 'string', 'max:5000'],
            'batch_year' => ['nullable', 'string', 'max:50'],
            'batch_section' => ['nullable', Rule::in(['1', '2'])],
            'status' => ['nullable', Rule::in([
                'Active', 'On hold', 'Reintegrated', 'Inactive', 'Under Review',
                'Disengaged', 'Pending', 'Suspended', 'Completed', 'Deceased', 'Relocated',
            ])],
            'autosave' => ['nullable', 'boolean'],
        ];
    }
}
