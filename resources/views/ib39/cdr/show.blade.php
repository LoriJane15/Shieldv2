@extends('layouts.skydash-v')
@section('title', 'CDR Workspace')
@section('heading', 'Custodial Debriefing Report')

@section('content')
<div class="row justify-content-center"><div class="col-xl-10">
    @if (session('status'))<div class="alert alert-success" role="status">{{ session('status') }}</div>@endif
    <div class="card shadow-sm mb-4"><div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start" style="gap:1rem">
            <div><p class="text-muted text-uppercase small mb-1">CDR Workspace</p><h2 class="h4 mb-2">{{ $cdr->surfacedFormerRebel->reference_number }}</h2><p class="mb-0">Status: <strong>{{ $cdr->status->value }}</strong></p></div>
            <div class="d-flex flex-wrap" style="gap:.5rem">
                @if ($cdr->status === \App\Enums\Ib39CdrStatus::Pending)
                    <form method="POST" action="{{ route('ib39.cdr.start', $cdr) }}">@csrf<button class="btn btn-primary" type="submit">Start processing</button></form>
                @elseif ($cdr->status === \App\Enums\Ib39CdrStatus::Ongoing)
                    <a class="btn btn-primary" href="{{ route('ib39.cdr.edit', $cdr) }}">Edit draft</a>
                    <a class="btn btn-danger" href="{{ route('ib39.cdr.finalization.review', $cdr) }}">Submit as Final</a>
                @endif
                @if ($cdr->status === \App\Enums\Ib39CdrStatus::Completed && $cdr->currentFinalVersion)
                    <a class="btn btn-outline-primary" href="{{ route('ib39.cdr.documents.preview', [$cdr, $cdr->currentFinalVersion]) }}">View final copy</a>
                    @if ($cdr->currentFinalVersion->source_type === \App\Enums\Ib39CdrDocumentSource::Generated)
                        <a class="btn btn-outline-secondary" href="{{ route('ib39.cdr.documents.print', [$cdr, $cdr->currentFinalVersion]) }}" target="_blank" rel="noopener">Print final copy</a>
                    @else
                        <a class="btn btn-outline-secondary" href="{{ route('ib39.cdr.documents.download', [$cdr, $cdr->currentFinalVersion]) }}">Download final copy</a>
                    @endif
                @else
                    <a class="btn btn-outline-primary" href="{{ route('ib39.cdr.preview', $cdr) }}">Preview draft</a>
                    <a class="btn btn-outline-secondary" href="{{ route('ib39.cdr.print', $cdr) }}" target="_blank" rel="noopener">Print draft</a>
                @endif
            </div>
        </div>
    </div></div>
    <div class="card shadow-sm mb-4"><div class="card-body"><h3 class="h5">{{ $cdr->status === \App\Enums\Ib39CdrStatus::Completed ? 'Final document details' : 'Draft details' }}</h3><dl class="row mb-0">
        <dt class="col-sm-4">Schema version</dt><dd class="col-sm-8">{{ $cdr->form?->schema_version ?? 1 }}</dd>
        <dt class="col-sm-4">Last saved</dt><dd class="col-sm-8">{{ $cdr->form?->updated_at?->format('F d, Y h:i A') ?? 'Not yet saved' }}</dd>
        <dt class="col-sm-4">Last editor</dt><dd class="col-sm-8">{{ $cdr->form?->lastEditor?->name ?? 'Not available' }}</dd>
        @if($cdr->status === \App\Enums\Ib39CdrStatus::Completed)
            <dt class="col-sm-4">Final version</dt><dd class="col-sm-8">{{ $cdr->currentFinalVersion?->version_number }}</dd>
            <dt class="col-sm-4">Finalized</dt><dd class="col-sm-8">{{ $cdr->completed_at?->format('F d, Y h:i A') }}</dd>
        @endif
    </dl></div></div>

    @can('uploadFinal', $cdr)
        <div class="card shadow-sm mb-4"><div class="card-body">
            <h3 class="h5">Upload Completed CDR</h3>
            <div class="alert alert-warning">Successful upload will mark this CDR Completed and lock ordinary draft editing. The structured draft does not need to be complete.</div>
            <form method="POST" action="{{ route('ib39.cdr.documents.upload', $cdr) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group"><label for="final_document">Final CDR file</label><input id="final_document" class="form-control-file @error('document') is-invalid @enderror" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>@error('document')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror<small class="form-text text-muted">PDF, JPEG, or PNG; maximum 20 MiB.</small></div>
                <div class="form-check mb-3"><input id="upload_confirmed" class="form-check-input" type="checkbox" name="confirmed" value="1" required><label class="form-check-label" for="upload_confirmed">This is the final CDR.</label></div>
                @error('confirmed')<div class="text-danger mb-2">{{ $message }}</div>@enderror
                <button class="btn btn-danger" type="submit">Upload Completed CDR</button>
            </form>
        </div></div>
    @endcan

    @can('replaceFinal', $cdr)
        <div class="card shadow-sm mb-4"><div class="card-body">
            <h3 class="h5">Replace Final CDR</h3>
            <p class="text-muted">The current final remains active unless the replacement succeeds. Earlier versions are preserved.</p>
            <form method="POST" action="{{ route('ib39.cdr.documents.replace', $cdr) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group"><label for="replacement_document">Replacement file</label><input id="replacement_document" class="form-control-file @error('document') is-invalid @enderror" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required></div>
                <div class="form-group"><label for="replacement_reason">Replacement reason</label><textarea id="replacement_reason" class="form-control @error('replacement_reason') is-invalid @enderror" name="replacement_reason" maxlength="2000" required>{{ old('replacement_reason') }}</textarea>@error('replacement_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-check mb-3"><input id="replacement_confirmed" class="form-check-input" type="checkbox" name="confirmed" value="1" required><label class="form-check-label" for="replacement_confirmed">I confirm this replacement final CDR.</label></div>
                <button class="btn btn-danger" type="submit">Replace Final CDR</button>
            </form>
        </div></div>
    @endcan

    @can('viewVersionHistory', $cdr)
        <div class="card shadow-sm mb-4"><div class="card-body">
            <h3 class="h5">Final-document version history</h3>
            @if ($cdr->documentVersions->isEmpty())
                <p class="text-muted mb-0">No final-document versions have been created.</p>
            @else
                <div class="table-responsive"><table class="table table-sm table-bordered">
                    <thead><tr><th>Version</th><th>Source</th><th>File</th><th>Type / size</th><th>Finalized</th><th>By</th><th>Replacement</th><th>Actions</th></tr></thead>
                    <tbody>
                    @foreach ($cdr->documentVersions as $version)
                        <tr>
                            <td>v{{ $version->version_number }} @if($cdr->current_final_version_id === $version->id)<span class="badge badge-success">Current</span>@endif</td>
                            <td>{{ $version->source_type === \App\Enums\Ib39CdrDocumentSource::Generated ? 'System Generated' : 'Direct Upload' }}</td>
                            <td>{{ $version->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded ? $version->original_filename : 'System-authored CDR' }}</td>
                            <td>{{ $version->mime_type }}<br>{{ number_format($version->size_bytes / 1024, 1) }} KiB</td>
                            <td>{{ $version->finalized_at?->format('F d, Y h:i A') }}</td>
                            <td>{{ $version->creator?->name ?? 'Unavailable' }}</td>
                            <td>@if($version->replacesVersion)Replaces v{{ $version->replacesVersion->version_number }}<br>{{ $version->replacement_reason }}@else—@endif</td>
                            <td>
                                <a href="{{ route('ib39.cdr.documents.preview', [$cdr, $version]) }}">Preview</a>
                                @if($version->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded)
                                    · <a href="{{ route('ib39.cdr.documents.download', [$cdr, $version]) }}">Download</a>
                                @else
                                    · <a href="{{ route('ib39.cdr.documents.print', [$cdr, $version]) }}" target="_blank" rel="noopener">Print</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table></div>
            @endif
        </div></div>
    @endcan
    <a class="btn btn-light" href="{{ route('ib39.fr-profiles.show', $cdr->surfacedFormerRebel) }}">Back to FR profile</a>
</div></div>
@endsection
