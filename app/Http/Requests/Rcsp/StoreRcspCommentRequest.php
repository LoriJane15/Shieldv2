<?php

namespace App\Http\Requests\Rcsp;

use Illuminate\Foundation\Http\FormRequest;

class StoreRcspCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $form = $this->route('form');

        return $form && ($this->user()?->can('comment', $form) ?? false);
    }

    public function rules(): array
    {
        return ['text' => ['required', 'string', 'max:5000']];
    }
}
