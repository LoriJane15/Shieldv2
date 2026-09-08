@extends('layouts.skydash-v')
@section('title', 'CDR Finalization Review')
@section('heading', 'CDR Finalization Review')
@section('content')
<div class="row justify-content-center"><div class="col-xl-9">
    <div class="alert alert-danger" role="alert"><h2 class="h5">Final submission will complete and lock this CDR.</h2><p class="mb-0">After confirmation, ordinary editing, draft saving, starting, and photo replacement will be unavailable. Replacement is not part of this stage.</p></div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="card shadow-sm mb-4"><div class="card-body"><h3 class="h5">Missing information review</h3>
    @forelse($missingFields as $section => $fields)<section class="mb-3"><h4 class="h6">{{ $section }}</h4><ul class="mb-0">@foreach($fields as $field)<li>{{ $field }}</li>@endforeach</ul></section>@empty<p class="mb-0">No missing editable fields or applicable tables were found. Confirmation is still required.</p>@endforelse
    </div></div>
    <form method="POST" action="{{ route('ib39.cdr.finalize',$cdr) }}">@csrf<input type="hidden" name="draft_fingerprint" value="{{ $draftFingerprint }}"><div class="form-check mb-3"><input class="form-check-input" id="confirm-final" type="checkbox" name="confirmed" value="1" required><label class="form-check-label font-weight-bold" for="confirm-final">I understand that this will create the immutable final copy and lock ordinary CDR changes.</label></div><button class="btn btn-danger" type="submit">Confirm and Submit as Final</button> <a class="btn btn-light" href="{{ route('ib39.cdr.edit',$cdr) }}">Return to editor</a></form>
</div></div>
@endsection
