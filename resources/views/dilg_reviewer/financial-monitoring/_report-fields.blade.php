@php
    $value = fn (string $key, $fallback = null) => old($key, $item?->{$key} ?? $fallback);
@endphp
<label>Reporting month *<input class="form-control" type="month" name="reporting_month" value="{{ $value('reporting_month') instanceof \Carbon\CarbonInterface ? $value('reporting_month')->format('Y-m') : str($value('reporting_month'))->substr(0,7) }}" required></label>
<label>Form 11 reference<input class="form-control" name="form_11_reference" value="{{ $value('form_11_reference') }}" maxlength="255"></label>
<label>Status *<select class="form-control" name="status" required>@foreach(\App\Models\EclipRegionalDisbursementReport::STATUSES as $status)<option value="{{ $status }}" @selected($value('status','preparing') === $status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select></label>
<label>Submission date<input class="form-control" type="date" name="submitted_at" value="{{ $value('submitted_at') instanceof \Carbon\CarbonInterface ? $value('submitted_at')->format('Y-m-d') : $value('submitted_at') }}"></label>
<label>Return date<input class="form-control" type="date" name="returned_at" value="{{ $value('returned_at') instanceof \Carbon\CarbonInterface ? $value('returned_at')->format('Y-m-d') : $value('returned_at') }}"></label>
<label>Resubmission date<input class="form-control" type="date" name="resubmitted_at" value="{{ $value('resubmitted_at') instanceof \Carbon\CarbonInterface ? $value('resubmitted_at')->format('Y-m-d') : $value('resubmitted_at') }}"></label>
<label>Acceptance date<input class="form-control" type="date" name="accepted_at" value="{{ $value('accepted_at') instanceof \Carbon\CarbonInterface ? $value('accepted_at')->format('Y-m-d') : $value('accepted_at') }}"></label>
<label class="field-wide">Return reason<textarea class="form-control" name="return_reason" rows="2" maxlength="5000">{{ $value('return_reason') }}</textarea></label>
<label class="field-full">Remarks / recipients / corrections<textarea class="form-control" name="remarks" rows="2" maxlength="5000">{{ $value('remarks') }}</textarea></label>
