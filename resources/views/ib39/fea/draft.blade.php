@extends('layouts.skydash-v')

@section('title', 'FEA Draft')
@section('heading', 'FEA Draft')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div><h1 class="h3 mb-1">{{ $document->document_type->label() }}</h1><p class="text-muted mb-0">Encrypted working draft · Revision {{ $document->draft_revision }}</p></div>
        <a class="btn btn-light" href="{{ route('ib39.fea.show', $fea) }}">Back to FEA Record</a>
    </div>

    <div class="alert alert-info d-flex justify-content-between align-items-center"><span>Preview and print use only the last saved draft. Save your changes first.</span><span><a class="btn btn-sm btn-outline-primary" href="{{ route('ib39.fea.documents.draft.preview', [$fea, $document]) }}">Preview Saved Draft</a> <a class="btn btn-sm btn-outline-secondary" href="{{ route('ib39.fea.documents.draft.print', [$fea, $document]) }}">Print Saved Draft</a></span></div>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>The draft was not saved.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if($document->document_type === \App\Enums\Ib39FeaDocumentType::Justification)
        @include('ib39.fea.partials.supporting-photo-upload', ['slot' => \App\Enums\Ib39FeaUploadSlot::JustificationSurrendered, 'render' => 'form'])
        @include('ib39.fea.partials.supporting-photo-upload', ['slot' => \App\Enums\Ib39FeaUploadSlot::JustificationComparison, 'render' => 'form'])
    @endif

    <form method="POST" action="{{ route('ib39.fea.documents.draft.update', [$fea, $document]) }}" class="card" id="draft-form">
        @csrf @method('PUT')
        <input type="hidden" name="revision" value="{{ $document->draft_revision }}">
        <div class="card-body">
            @if($document->document_type === \App\Enums\Ib39FeaDocumentType::Cvif)
                <div class="text-center mb-4"><h2 class="h4">COST VALUATION OF INVENTORIED FIREARMS</h2></div>
                <div class="border p-3 mb-4">THIS IS TO CERTIFY that [Name of FR or FVE]; with RM No. [RM No.]<br>The owner of the firearm is described below:<br><br>THAT THE ABOVE-DESCRIBED firearms has undergone inventory and technical on [Date of inspection] at [Place of inspection] with the following findings:<br><br>THAT UPON DUE DELIBERATION, The Valuation Committee finds the amount [Amount in words] (Php: [Cost valuation]) as the cost valuation for the said firearm.</div>
            @elseif($document->document_type === \App\Enums\Ib39FeaDocumentType::Justification)
                <div class="text-center mb-4"><small>Form 25</small><h2 class="h4">JUSTIFICATION ON THE TIR AND CVC/CVIF</h2></div>
            @elseif($document->document_type === \App\Enums\Ib39FeaDocumentType::Tir)
                <h2 class="h4 text-center mb-4">TECHNICAL INSPECTION REPORT</h2>
            @else
                <h2 class="h4 text-center mb-4">PROPERTY TURN-IN SLIP</h2>
            @endif

            @foreach($fields as $field)
                @php($value = $draft[$field['key']] ?? null)
                @if($field['type'] === 'notice')
                    @php($photoSlot = $field['key'] === 'photo_notice_3' ? \App\Enums\Ib39FeaUploadSlot::JustificationSurrendered : \App\Enums\Ib39FeaUploadSlot::JustificationComparison)
                    <section class="border rounded p-3 mb-4"><strong>{{ $field['label'] }}</strong>@include('ib39.fea.partials.supporting-photo-upload', ['slot' => $photoSlot, 'render' => 'controls'])</section>
                @elseif($field['type'] === 'table')
                    <section class="mb-4" data-draft-table="{{ $field['key'] }}" data-next-index="{{ count($value ?? []) }}">
                        <label class="font-weight-bold">{{ $field['label'] }}</label>
                        <div class="table-responsive"><table class="table table-bordered"><thead><tr>@foreach($field['columns'] as $label)<th>{{ $label }}</th>@endforeach<th><span class="sr-only">Row action</span></th></tr></thead><tbody>
                        @foreach(($value ?? []) as $index => $row)<tr>@foreach($field['columns'] as $key => $label)<td><input class="form-control" name="draft[{{ $field['key'] }}][{{ $index }}][{{ $key }}]" value="{{ $row[$key] ?? '' }}" maxlength="500" @if($key === 'quantity') type="number" min="0" max="999999" @endif></td>@endforeach<td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Remove</button></td></tr>@endforeach
                        </tbody></table></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-add-row>Add row</button>
                        <template><tr>@foreach($field['columns'] as $key => $label)<td><input class="form-control" data-name="draft[{{ $field['key'] }}][__INDEX__][{{ $key }}]" maxlength="500" @if($key === 'quantity') type="number" min="0" max="999999" @endif></td>@endforeach<td><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>Remove</button></td></tr></template>
                    </section>
                @elseif($field['type'] === 'choice')
                    <div class="form-group"><label>{{ $field['label'] }}</label><div>@foreach($field['choices'] as $choice)<label class="mr-3"><input type="radio" name="draft[{{ $field['key'] }}]" value="{{ $choice }}" @checked($value === $choice)> {{ $choice }}</label>@endforeach</div></div>
                @else
                    <div class="form-group"><label for="field-{{ $field['key'] }}">{{ $field['label'] }}</label>
                        @if($field['type'] === 'textarea')<textarea id="field-{{ $field['key'] }}" name="draft[{{ $field['key'] }}]" class="form-control" maxlength="4000" rows="3">{{ $value }}</textarea>
                        @else<input id="field-{{ $field['key'] }}" name="draft[{{ $field['key'] }}]" class="form-control" value="{{ $value }}" maxlength="500" type="{{ in_array($field['type'], ['date', 'time'], true) ? $field['type'] : ($field['type'] === 'decimal' ? 'number' : 'text') }}" @if($field['type'] === 'decimal') min="0" max="999999999.99" step="0.01" @endif>@endif
                        @isset($field['title'])<small class="form-text text-muted">{{ $field['title'] }}</small>@endisset
                    </div>
                @endif
            @endforeach

            @if($document->document_type === \App\Enums\Ib39FeaDocumentType::Justification)
                <div class="border p-3 mb-4">THIS IS TO CERTIFY that the justification provided in the Cost Valuation Certificate(CVC) / Cost Valuation of Inventoried Firearms(CVIF), as prepared by <span data-preparer-name>[Prepared by name]</span>, has been reviewed by the PNP RSAO and found to be correct, accurate and sufficient to support the cost valuation reflected on the CVC/CVIF of the firearms enumerated above.</div>
            @endif
            @if($document->document_type === \App\Enums\Ib39FeaDocumentType::Ptis)
                <div class="border p-3 mb-4"><strong>LEGEND FOR REMARKS</strong><br>FWT — Unserviceable due to wear and tear<br>SER — Serviceable<br>R/C — Unserviceable Statement<br>R/S — Unserviceable Report on survey<br>EXC — In Excess of Authorized Allowance<br><br>I HEREBY CERTIFY that the article/s listed herein are turned-in under the circumstances indicated therein:<br><br>FOR THE COMMANDING OFFICER:<br>(signature above printed name) AFP/ PNP Representative<br><br>CONFIRMED BY:<br>(signature above printed name) (DILG Representative)<br><br>QUANTITIES SHOWN ABOVE IN ACTION HAVE BEEN RECEIVED:<br>(DATE :) For Station Supply or Classification Officer</div>
            @endif
            <button class="btn btn-primary" type="submit">Save Encrypted Draft</button>
        </div>
    </form>

    <section class="card mt-4"><div class="card-body"><h2 class="h5">Draft history</h2>@forelse($document->draftHistories as $history)<div class="border-top py-2">Revision {{ $history->revision }} · {{ $history->created_at->format('F d, Y · h:i A') }} · {{ $history->actor?->name ?: 'User unavailable' }}<br><small>Changed fields: {{ implode(', ', $history->changed_fields) ?: 'Initial blank draft' }}</small></div>@empty<p class="text-muted mb-0">No saved draft history.</p>@endforelse</div></section>
</div>
<script>
document.addEventListener('click', function (event) {
    const remove = event.target.closest('[data-remove-row]'); if (remove) { remove.closest('tr').remove(); return; }
    const add = event.target.closest('[data-add-row]'); if (!add) return;
    const section = add.closest('[data-draft-table]'); let index = Number(section.dataset.nextIndex); if (section.querySelectorAll('tbody tr').length >= 30) return;
    const fragment = section.querySelector('template').content.cloneNode(true); fragment.querySelectorAll('[data-name]').forEach(input => { input.name = input.dataset.name.replace('__INDEX__', index); input.removeAttribute('data-name'); });
    section.querySelector('tbody').appendChild(fragment); section.dataset.nextIndex = String(index + 1);
});
let draftDirty = false;
const draftForm = document.getElementById('draft-form');
draftForm?.addEventListener('input', function () { draftDirty = true; });
draftForm?.addEventListener('submit', function () { draftDirty = false; });
document.querySelectorAll('[data-supporting-photo-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (draftDirty && !window.confirm('This upload reloads the editor. Continue without saving your text changes?')) event.preventDefault();
    });
});
</script>
@endsection
