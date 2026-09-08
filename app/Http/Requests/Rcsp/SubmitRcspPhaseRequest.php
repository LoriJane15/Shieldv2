<?php

namespace App\Http\Requests\Rcsp;

use App\Models\RcspActivity;
use App\Models\RcspForm;
use App\Models\RcspPhase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;

class SubmitRcspPhaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $barangay = $this->route('rcspBarangay');

        return $barangay && ($this->user()?->can('update', $barangay) ?? false);
    }

    public function rules(): array
    {
        $barangay = $this->route('rcspBarangay');
        $phase = $barangay ? RcspPhase::where('catalog_key', $barangay->catalog_key)
            ->where('number', $barangay->current_phase)->first() : null;
        $activityIds = $phase ? RcspActivity::where('rcsp_phase_id', $phase->id)->pluck('id') : collect();
        $approvedIds = $phase && $barangay ? RcspForm::where('rcsp_barangay_id', $barangay->id)
            ->where('rcsp_phase_id', $phase->id)->where('status', 'approved')->pluck('rcsp_activity_id') : collect();
        $mutableIds = $activityIds->diff($approvedIds);

        $rules = [
            'phase_id' => ['required', 'integer', Rule::exists('rcsp_phases', 'id')],
            'conduct' => ['required', 'array', 'size:'.$mutableIds->count()],
            'evidence' => ['sometimes', 'array'],
        ];
        foreach ($mutableIds as $id) {
            $rules["conduct.$id"] = ['required', Rule::in(['yes', 'no', 'n/a'])];
            $rules["evidence.$id"] = ['nullable', File::types(['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])->max('25mb')];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $barangay = $this->route('rcspBarangay');
            $phase = RcspPhase::find($this->integer('phase_id'));
            if (! $phase || ! $barangay || $phase->catalog_key !== $barangay->catalog_key
                || $phase->number !== $barangay->current_phase) {
                $validator->errors()->add('phase_id', 'The submitted phase must be the barangay current phase in its catalog.');

                return;
            }
            $activityIds = RcspActivity::where('rcsp_phase_id', $phase->id)->pluck('id');
            $approvedIds = RcspForm::where('rcsp_barangay_id', $barangay->id)->where('rcsp_phase_id', $phase->id)
                ->where('status', 'approved')->pluck('rcsp_activity_id');
            $expected = $activityIds->diff($approvedIds)->map(fn ($id) => (string) $id)->sort()->values()->all();
            $conduct = collect(array_keys((array) $this->input('conduct', [])))->map(fn ($id) => (string) $id)->sort()->values()->all();
            $evidence = collect(array_keys((array) $this->file('evidence', [])))->map(fn ($id) => (string) $id)->sort()->values()->all();
            if ($expected === [] || $conduct !== $expected) {
                $validator->errors()->add('conduct', 'Conduct is required for every current-phase activity and no other activity is accepted.');
            }
            if (array_diff($evidence, $expected)) {
                $validator->errors()->add('evidence', 'An evidence file does not belong to the current phase.');
            }
            $allowed = ['_token', 'phase_id', 'conduct', 'evidence'];
            if (array_diff(array_keys($this->all()), $allowed)) {
                $validator->errors()->add('request', 'Unknown phase submission fields are not accepted.');
            }
        }];
    }
}
