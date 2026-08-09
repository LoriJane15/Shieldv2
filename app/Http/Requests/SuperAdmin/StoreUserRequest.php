<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('super_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'role' => ['required', Rule::in(['super_admin', 'admin', '39th_ib', 'gov_agency', 'lgu', 'mblrc', 'lswdo', 'japic', 'dilg_provincial_focal', 'dilg_regional', 'nboo_eclip_pmo', 'dilg_fms', 'local_eclip_committee', 'pnp', 'afp'])],
            'is_active' => ['sometimes', 'boolean'],
            'municipality_id' => ['nullable', 'required_if:role,lgu,lswdo,dilg_provincial_focal,local_eclip_committee', 'exists:municipalities,id'],
            'gov_agency_id' => ['nullable', 'required_if:role,gov_agency', 'exists:gov_agencies,id'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:ratio=1/1', 'max:5120'],
        ];
    }
}
