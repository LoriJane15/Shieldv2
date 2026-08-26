<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormerRebelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('mblrc') ?? false;
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'suffix' => ['nullable', Rule::in(['Jr.', 'Sr.', 'II', 'III'])],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'civil_status' => ['nullable', Rule::in(['Single', 'Married', 'Widowed', 'Separated'])],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'contact_num' => ['required', 'string', 'max:15', 'regex:/^\+?[0-9]+$/'],
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'barangay_id' => [
                'required',
                Rule::exists('barangays', 'id')
                    ->where('municipality_id', $this->input('municipality_id')),
            ],
            'province' => ['nullable', 'string', 'max:50'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'residential_address' => ['required', 'string', 'max:255'],
            'surrender_date' => ['required', 'date', 'before_or_equal:today'],
            'surrender_reason' => ['nullable', 'string', 'max:5000'],
            'batch_year' => ['nullable', 'string', 'max:50'],
            'batch_section' => ['nullable', Rule::in(['1', '2'])],
            'status' => ['nullable', Rule::in([
                'Active', 'On hold', 'Reintegrated', 'Inactive', 'Under Review',
                'Disengaged', 'Pending', 'Suspended', 'Completed', 'Deceased', 'Relocated',
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'birthdate.required' => 'Birthday is required so age can be calculated accurately.',
            'birthdate.before_or_equal' => 'Birthday cannot be in the future.',
            'contact_num.required' => 'Contact number is required.',
            'contact_num.regex' => 'Enter a valid contact number using digits only.',
            'municipality_id.required' => 'Select a municipality or city.',
            'barangay_id.required' => 'Select a barangay.',
            'barangay_id.exists' => 'The selected barangay does not belong to this municipality or city.',
            'residential_address.required' => 'Residential address is required.',
            'surrender_date.required' => 'Date of surrender is required.',
            'surrender_date.before_or_equal' => 'Date of surrender cannot be in the future.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $contact = $this->input('contact_num');

        $this->merge([
            'firstname' => $this->trimmed('firstname'),
            'middlename' => $this->trimmed('middlename'),
            'lastname' => $this->trimmed('lastname'),
            'nickname' => $this->trimmed('nickname'),
            'residential_address' => $this->trimmed('residential_address'),
            'surrender_reason' => $this->trimmed('surrender_reason'),
            'contact_num' => filled($contact)
                ? preg_replace('/[^0-9+]/', '', (string) $contact)
                : null,
        ]);
    }

    private function trimmed(string $field): ?string
    {
        $value = trim((string) $this->input($field, ''));

        return $value === '' ? null : $value;
    }
}
