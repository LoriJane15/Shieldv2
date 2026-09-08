<?php

namespace App\Http\Requests\Rcsp;

use App\Models\RcspForm;
use App\Models\RcspPhase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReviewRcspPhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $barangay = $this->route('rcspBarangay');

        return $barangay && ($this->user()?->can('review', $barangay) ?? false);
    }

    public function rules(): array
    {
        return [
            'phase_id' => ['required', 'integer', Rule::exists('rcsp_phases', 'id')],
            'statuses' => ['required', 'array', 'min:1'],
            'statuses.*' => ['required', Rule::in(['approved', 'disapproved', 'to be complied', 'to be conducted'])],
            'remarks' => ['sometimes', 'array'],
            'remarks.*' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $barangay = $this->route('rcspBarangay');
            $phase = RcspPhase::find($this->integer('phase_id'));
            if (! $phase || ! $barangay || $phase->catalog_key !== $barangay->catalog_key
                || $phase->number !== $barangay->current_phase) {
                $validator->errors()->add('phase_id', 'The review phase must be the barangay current phase in its catalog.');

                return;
            }
            $ids = collect(array_keys((array) $this->input('statuses', [])))->map(fn ($id) => (int) $id);
            $valid = RcspForm::where('rcsp_barangay_id', $barangay->id)->where('rcsp_phase_id', $phase->id)
                ->whereIn('id', $ids)->count();
            if ($valid !== $ids->unique()->count()) {
                $validator->errors()->add('statuses', 'Every reviewed form must belong to the selected barangay and phase.');
            }
            $remarks = (array) $this->input('remarks', []);
            if (array_diff(array_keys($remarks), $ids->all())) {
                $validator->errors()->add('remarks', 'A remark references an unknown form.');
            }
            foreach ((array) $this->input('statuses', []) as $id => $status) {
                if ($status !== 'approved' && trim((string) ($remarks[$id] ?? '')) === '') {
                    $validator->errors()->add("remarks.$id", 'Remarks are required when returning or disapproving an activity.');
                }
            }
            if (array_diff(array_keys($this->all()), ['_token', 'phase_id', 'statuses', 'remarks'])) {
                $validator->errors()->add('request', 'Unknown review fields are not accepted.');
            }
        }];
    }
}
