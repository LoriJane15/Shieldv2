<?php

namespace App\Http\Requests\Rcsp;

use App\Models\Barangay;
use App\Models\RcspBarangay;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRcspBarangayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', RcspBarangay::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'barangay_id' => [
                'required', 'integer',
                Rule::exists('barangays', 'id')->where('municipality_id', $this->user()->municipality_id),
                Rule::unique('rcsp_barangays', 'barangay_id'),
            ],
        ];
    }

    public function catalogKey(): ?string
    {
        $barangay = Barangay::with('municipality')->find($this->integer('barangay_id'));

        return $barangay?->municipality?->name === 'DEMO Municipality'
            && str_starts_with($barangay->name, 'DEMO ') ? 'rcsp-demo-v1' : null;
    }
}
