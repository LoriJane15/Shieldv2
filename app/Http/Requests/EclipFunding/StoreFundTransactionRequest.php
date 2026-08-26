<?php

namespace App\Http\Requests\EclipFunding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageFunding', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['allocation', 'transfer'])],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2', 'max:9999999999999.99'],
            'reference_number' => ['required', 'string', 'max:150', Rule::unique('eclip_fund_transactions')->where(fn ($query) => $query->where('type', $this->type))],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'nta_received_date' => ['nullable', 'required_if:type,transfer', 'date', 'before_or_equal:today'],
            'recipient_office' => ['nullable', 'required_if:type,transfer', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'proof' => ['nullable', 'required_if:type,transfer', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['reference_number' => trim((string) $this->reference_number)]);
    }
}
