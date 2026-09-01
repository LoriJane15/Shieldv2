@php
    $primary = $document->currentDraftVersion;
    $isPhoto = in_array($document->document_type, [\App\Enums\Ib39FeaDocumentType::FirearmPhoto, \App\Enums\Ib39FeaDocumentType::FrWithFirearmPhoto], true);
    $primaryVersions = $document->versions->where('slot', \App\Enums\Ib39FeaUploadSlot::Primary);
    $canUpload = auth()->user()->can('uploadDraft', [$document, $fea]);
@endphp
<section class="draft-uploads mt-3" aria-labelledby="upload-title-{{ $document->id }}">
    <h4 id="upload-title-{{ $document->id }}">Private Draft Upload — DRAFT — NOT FINAL</h4>
    <p>{{ $isPhoto ? 'JPEG or PNG only.' : 'PDF only.' }} Maximum 20 MiB. Files are stored privately as immutable versions.</p>
    @if($primary)
        <div class="current-upload"><strong>Current: Version {{ $primary->version_number }}</strong><span>{{ $primary->original_filename }} · {{ $primary->mime_type }} · {{ number_format($primary->size_bytes / 1024, 1) }} KiB · {{ $primary->created_at->format('F d, Y · h:i A') }} · {{ $primary->uploader?->name ?? 'User unavailable' }}</span></div>
        <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('ib39.fea.documents.versions.preview', [$fea, $document, $primary]) }}">Preview Draft</a>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('ib39.fea.documents.versions.download', [$fea, $document, $primary]) }}">Download Draft</a>
    @else<div class="empty-history">No private draft file uploaded.</div>@endif
    @if($canUpload)<form method="POST" enctype="multipart/form-data" action="{{ route('ib39.fea.documents.versions.store', [$fea, $document]) }}" class="upload-form">
        @csrf
        @if($primary)<input type="hidden" name="expected_current_version_id" value="{{ $primary->id }}">@endif
        <label for="draft-file-{{ $document->id }}">{{ $primary ? 'Replacement draft file' : 'Draft file' }}</label>
        <input id="draft-file-{{ $document->id }}" class="form-control-file" type="file" name="file" accept="{{ $isPhoto ? '.jpg,.jpeg,.png,image/jpeg,image/png' : '.pdf,application/pdf' }}" required>
        @if($primary)<label for="replacement-reason-{{ $document->id }}">Reason for replacement</label><textarea id="replacement-reason-{{ $document->id }}" name="replacement_reason" maxlength="2000" class="form-control" required></textarea>@endif
        <button class="btn btn-sm btn-primary mt-2" type="submit">{{ $primary ? 'Store New Draft Version' : 'Upload Draft' }}</button>
    </form>@endif
    @if($primaryVersions->isNotEmpty())
        <details class="version-history"><summary>Draft version history ({{ $primaryVersions->count() }})</summary>
            @foreach($primaryVersions as $version)<div><strong>Version {{ $version->version_number }} — DRAFT — NOT FINAL</strong><span>{{ $version->original_filename }} · {{ $version->mime_type }} · {{ number_format($version->size_bytes / 1024, 1) }} KiB · {{ $version->created_at->format('F d, Y · h:i A') }} · {{ $version->uploader?->name ?? 'User unavailable' }}</span>@if($version->replacement_reason)<span>Replacement reason: {{ $version->replacement_reason }}</span>@endif <a href="{{ route('ib39.fea.documents.versions.preview', [$fea, $document, $version]) }}" target="_blank" rel="noopener">Preview</a> <a href="{{ route('ib39.fea.documents.versions.download', [$fea, $document, $version]) }}">Download</a></div>@endforeach
        </details>
    @endif
    @if($document->document_type === \App\Enums\Ib39FeaDocumentType::Justification)
        @php($comparison = $document->currentSupportingPhotoVersion)
        @php($comparisonVersions = $document->versions->where('slot', \App\Enums\Ib39FeaUploadSlot::JustificationComparison))
        <div class="support-upload"><h4>Section 4 Supporting Comparison Photo — DRAFT — NOT FINAL</h4><p>This supports Justification Section 4 and is not an additional FEA requirement. JPEG or PNG only.</p>
            @if($comparison)<div class="current-upload"><strong>Current: Version {{ $comparison->version_number }}</strong><span>{{ $comparison->original_filename }} · {{ $comparison->mime_type }} · {{ number_format($comparison->size_bytes / 1024, 1) }} KiB · {{ $comparison->created_at->format('F d, Y · h:i A') }} · {{ $comparison->uploader?->name ?? 'User unavailable' }}</span></div><a href="{{ route('ib39.fea.documents.versions.preview', [$fea, $document, $comparison]) }}" target="_blank" rel="noopener">Preview supporting photo</a> <a href="{{ route('ib39.fea.documents.versions.download', [$fea, $document, $comparison]) }}">Download supporting photo</a>@endif
            @if($canUpload)<form method="POST" enctype="multipart/form-data" action="{{ route('ib39.fea.documents.comparison-versions.store', [$fea, $document]) }}" class="upload-form">@csrf
                @if($comparison)<input type="hidden" name="expected_current_version_id" value="{{ $comparison->id }}">@endif
                <label for="comparison-file-{{ $document->id }}">{{ $comparison ? 'Replacement comparison photo' : 'Comparison photo' }}</label><input id="comparison-file-{{ $document->id }}" class="form-control-file" type="file" name="file" accept=".jpg,.jpeg,.png,image/jpeg,image/png" required>
                @if($comparison)<label for="comparison-reason-{{ $document->id }}">Reason for replacement</label><textarea id="comparison-reason-{{ $document->id }}" name="replacement_reason" maxlength="2000" class="form-control" required></textarea>@endif
                <button class="btn btn-sm btn-primary mt-2" type="submit">Store Supporting Photo</button>
            </form>@endif
            @if($comparisonVersions->isNotEmpty())<details class="version-history"><summary>Supporting-photo version history ({{ $comparisonVersions->count() }})</summary>@foreach($comparisonVersions as $version)<div><strong>Version {{ $version->version_number }} — DRAFT — NOT FINAL</strong><span>{{ $version->original_filename }} · {{ $version->created_at->format('F d, Y · h:i A') }} · {{ $version->uploader?->name ?? 'User unavailable' }}</span>@if($version->replacement_reason)<span>Replacement reason: {{ $version->replacement_reason }}</span>@endif <a href="{{ route('ib39.fea.documents.versions.preview', [$fea, $document, $version]) }}" target="_blank" rel="noopener">Preview</a> <a href="{{ route('ib39.fea.documents.versions.download', [$fea, $document, $version]) }}">Download</a></div>@endforeach</details>@endif
        </div>
    @endif
</section>
