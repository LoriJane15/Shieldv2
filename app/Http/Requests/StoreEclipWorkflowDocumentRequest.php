<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEclipWorkflowDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('uploadWorkflowDocument', $this->route('activity')) ?? false;
    }

    public function rules(): array
    {
        $types = $this->route('activity')?->required_documents ?? [];

        return [
            'document_type' => ['required', 'string', Rule::in($types)],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
