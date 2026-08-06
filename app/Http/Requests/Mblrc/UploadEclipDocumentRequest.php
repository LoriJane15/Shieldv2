<?php

namespace App\Http\Requests\Mblrc;

use Illuminate\Foundation\Http\FormRequest;

class UploadEclipDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('uploadDocument', $this->route('eclipCase')) ?? false;
    }

    public function rules(): array
    {
        return [
            'requirement_id' => ['required', 'integer', 'exists:eclip_document_requirements,id'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
