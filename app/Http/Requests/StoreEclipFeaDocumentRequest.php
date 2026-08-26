<?php

namespace App\Http\Requests;

use App\Models\EclipFeaDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipFeaDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('uploadFeaDocument', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', Rule::in(EclipFeaDocument::TYPES)],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
