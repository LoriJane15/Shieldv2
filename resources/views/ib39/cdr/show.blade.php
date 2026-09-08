@extends('layouts.skydash-v')
@section('title', 'CDR Workspace')
@section('heading', 'Custodial Debriefing Report')

@push('styles')
<style>
    .cdr-workspace-container{max-width:1240px;margin:0 auto;padding-bottom:2.5rem}
    .module-nav-top{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem}
    .module-back-link{display:inline-flex;align-items:center;gap:.45rem;color:#4a5568;font-size:.84rem;font-weight:700;text-decoration:none!important;transition:color .15s ease}
    .module-back-link:hover{color:#401595}
    .module-back-link i{font-size:1.15rem}
    .module-breadcrumb{display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#718096;margin:0;padding:0;list-style:none}
    .module-breadcrumb li a{color:#718096;text-decoration:none;font-weight:600}
    .module-breadcrumb li a:hover{color:#401595}
    .module-breadcrumb li.active{color:#2d3748;font-weight:700}
    .module-breadcrumb .separator{color:#cbd5e0;font-size:.7rem}

    .cdr-hero{background:linear-gradient(125deg,#152a4d 0%,#1e3a6a 55%,#182848 100%);border-radius:16px;box-shadow:0 10px 28px rgba(21,42,77,.15);color:#fff;overflow:hidden;padding:1.4rem 1.7rem;position:relative;margin-bottom:1.5rem}
    .hero-top-row{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.25rem;position:relative;z-index:1}
    .hero-main{display:flex;align-items:center;gap:1.15rem;min-width:0}
    .hero-icon-box{display:flex;align-items:center;justify-content:center;width:58px;height:58px;flex:0 0 58px;border-radius:14px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:#45e0ba;font-size:1.65rem}
    .hero-eyebrow{color:#ff9a62;font-size:.64rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
    .cdr-hero h1{color:#fff;font-size:1.52rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.25rem;line-height:1.2}
    .cdr-hero p{color:#bac7e5;font-size:.82rem;margin:0;display:flex;align-items:center;gap:.65rem;flex-wrap:wrap}
    .hero-status-tag{display:inline-flex;align-items:center;gap:.35rem;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.22);color:#fff;padding:.2rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700}

    .hero-actions{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}
    .hero-btn{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;padding:.58rem 1.1rem;border-radius:9px;font-size:.8rem;font-weight:750;text-decoration:none!important;transition:all .15s ease;cursor:pointer}
    .hero-btn-primary{background:#fff;border:1px solid rgba(255,255,255,.8);color:#280274;box-shadow:0 4px 14px rgba(0,0,0,.15)}
    .hero-btn-primary:hover{background:#f1ebfa;color:#401595}
    .hero-btn-danger{background:#dc2626;border:1px solid #dc2626;color:#fff;box-shadow:0 4px 14px rgba(220,38,38,.25)}
    .hero-btn-danger:hover{background:#b91c1c;border-color:#b91c1c;color:#fff}
    .hero-btn-glass{background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff}
    .hero-btn-glass:hover{background:rgba(255,255,255,.22);color:#fff}

    .panel-card{border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 4px 18px rgba(15,23,42,.035);background:#fff;overflow:hidden;margin-bottom:1.35rem}
    .panel-card .card-body{padding:1.4rem}
    .section-heading{display:flex;align-items:center;margin-bottom:1.25rem}
    .section-icon{display:flex;align-items:center;justify-content:center;width:42px;height:42px;flex:0 0 42px;border-radius:11px;background:#f3edf8;color:#401595;font-size:1.25rem;margin-right:.9rem}
    .section-title{color:#1e293b;font-size:.96rem;font-weight:750;margin:0 0 .15rem}
    .section-subtitle{color:#64748b;font-size:.74rem;margin:0}

    .overview-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.85rem}
    .overview-item{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.85rem 1rem}
    .overview-item small{color:#718096;display:flex;align-items:center;gap:.35rem;font-size:.66rem;font-weight:750;letter-spacing:.04em;text-transform:uppercase}
    .overview-item small i{font-size:.85rem;color:#8a97aa}
    .overview-item strong{color:#1e293b;display:block;font-size:.88rem;font-weight:750;margin-top:.35rem;overflow-wrap:anywhere}

    .alert-amber{display:flex;align-items:flex-start;gap:.75rem;background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f59e0b;border-radius:10px;padding:.85rem 1rem;margin-bottom:1.25rem;color:#92400e;font-size:.78rem;line-height:1.45}
    .alert-amber i{color:#f59e0b;font-size:1.15rem;margin-top:.1rem}

    .upload-zone-modern{background:#f8fafc;border:2px dashed #cbd5e1;border-radius:12px;padding:1.25rem;text-align:center;transition:all .15s ease;position:relative;cursor:pointer}
    .upload-zone-modern:hover{border-color:#401595;background:#faf7fe}
    .upload-zone-icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:50%;background:#f1ebfa;color:#401595;font-size:1.35rem;margin-bottom:.5rem}
    .upload-zone-title{color:#1e293b;font-size:.84rem;font-weight:750;margin-bottom:.2rem}
    .upload-zone-hint{color:#64748b;font-size:.72rem;margin:0}
    .upload-zone-input{position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;cursor:pointer}
    .file-selected-badge{display:none;align-items:center;justify-content:center;gap:.4rem;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:.35rem .75rem;border-radius:8px;font-size:.76rem;font-weight:700;margin-top:.6rem}

    .confirm-pill{display:inline-flex;align-items:center;gap:.55rem;background:#f8fafc;border:1px solid #d8e0ea;border-radius:9px;padding:.6rem 1.1rem;margin-bottom:1.25rem;cursor:pointer;font-size:.8rem;font-weight:650;color:#334155;transition:all .15s ease}
    .confirm-pill:has(input:checked){background:#f1ebfa;border-color:#401595;color:#401595}
    .confirm-pill input{accent-color:#401595;margin:0;width:15px;height:15px}

    .btn-submit-action{display:inline-flex;align-items:center;justify-content:center;gap:.45rem;background:#dc2626;border:1px solid #dc2626;color:#fff;font-size:.82rem;font-weight:750;padding:.65rem 1.4rem;border-radius:9px;box-shadow:0 4px 12px rgba(220,38,38,.2);cursor:pointer;transition:all .15s ease}
    .btn-submit-action:hover{background:#b91c1c;border-color:#b91c1c;color:#fff}

    .version-item-card{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.9rem 1.05rem;margin-bottom:.75rem}
    .version-item-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.35rem}
    .version-badge-pill{display:inline-flex;align-items:center;gap:.35rem;font-size:.78rem;font-weight:800;color:#1e293b}
    .version-tag-current{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:.15rem .45rem;border-radius:12px;font-size:.65rem;font-weight:750}
    .version-source-tag{font-size:.68rem;font-weight:700;color:#64748b;background:#edf2f7;padding:.15rem .45rem;border-radius:6px}
    .version-filename{color:#334155;font-size:.76rem;font-weight:650;margin-bottom:.25rem;overflow-wrap:anywhere}
    .version-meta{display:flex;align-items:center;flex-wrap:wrap;gap:.65rem;color:#718096;font-size:.68rem}
    .version-meta span{display:inline-flex;align-items:center;gap:.25rem}
    .version-actions{display:flex;align-items:center;gap:.55rem;margin-top:.6rem;padding-top:.5rem;border-top:1px dashed #e2e8f0}
    .version-action-btn{display:inline-flex;align-items:center;gap:.3rem;font-size:.72rem;font-weight:750;color:#401595;text-decoration:none!important}
    .version-action-btn:hover{color:#280274;text-decoration:underline!important}

    .empty-state-box{text-align:center;padding:2.5rem 1rem;color:#8492a6}
    .empty-state-icon{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;border-radius:50%;background:#f1f5f9;color:#94a3b8;font-size:1.6rem;margin-bottom:.65rem}

    .bottom-back-action{margin-top:1.5rem}
    .btn-back-clean{display:inline-flex;align-items:center;gap:.45rem;background:#fff;border:1px solid #d8e0ea;color:#475569;font-size:.82rem;font-weight:650;padding:.6rem 1.15rem;border-radius:9px;text-decoration:none!important;transition:all .15s ease}
    .btn-back-clean:hover{background:#f8fafc;color:#1e293b}

    @media(max-width:991px){.overview-grid{grid-template-columns:1fr}}
    @media(max-width:767px){.cdr-hero{padding:1.2rem}.hero-top-row{flex-direction:column;align-items:flex-start}.hero-actions{width:100%}.hero-btn{flex:1;width:100%}.panel-card .card-body{padding:1.1rem}}
</style>
@endpush

@section('content')
<div class="cdr-workspace-container">
    {{-- Top Navigation & History --}}
    <div class="module-nav-top">
        <a href="{{ route('ib39.fr-profiles.show', $cdr->surfacedFormerRebel) }}" class="module-back-link">
            <i class="mdi mdi-arrow-left"></i>
            <span>Back to FR profile</span>
        </a>
        <ol class="module-breadcrumb" aria-label="Breadcrumb">
            <li><a href="{{ route('ib39.dashboard') }}">Dashboard</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li><a href="{{ route('ib39.fr-profiles.index') }}">FR Profiles</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li><a href="{{ route('ib39.fr-profiles.show', $cdr->surfacedFormerRebel) }}">{{ $cdr->surfacedFormerRebel->reference_number }}</a></li>
            <li class="separator"><i class="mdi mdi-chevron-right"></i></li>
            <li class="active" aria-current="page">CDR Workspace</li>
        </ol>
    </div>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center mb-4" role="status">
            <i class="mdi mdi-check-circle-outline mr-2 font-weight-bold"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Modern Hero Banner --}}
    <header class="cdr-hero">
        <div class="hero-top-row">
            <div class="hero-main">
                <div class="hero-icon-box">
                    <i class="mdi mdi-file-document-outline" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="hero-eyebrow mb-1">Custodial Debriefing Report · 39th IB</div>
                    <h1 class="mb-1">{{ $cdr->surfacedFormerRebel->reference_number }}</h1>
                    <p>
                        <span>Current Status: <strong class="text-white">{{ $cdr->status->value }}</strong></span>
                        <span>·</span>
                        <span>{{ $cdr->surfacedFormerRebel->display_name }}</span>
                    </p>
                </div>
            </div>
            <div class="hero-actions">
                @if ($cdr->status === \App\Enums\Ib39CdrStatus::Pending)
                    <form method="POST" action="{{ route('ib39.cdr.start', $cdr) }}" class="d-inline">
                        @csrf
                        <button class="hero-btn hero-btn-primary" type="submit">
                            <i class="mdi mdi-play"></i>
                            <span>Start processing</span>
                        </button>
                    </form>
                @elseif ($cdr->status === \App\Enums\Ib39CdrStatus::Ongoing)
                    <a class="hero-btn hero-btn-primary" href="{{ route('ib39.cdr.edit', $cdr) }}">
                        <i class="mdi mdi-pencil-outline"></i>
                        <span>Edit draft</span>
                    </a>
                    <a class="hero-btn hero-btn-danger" href="{{ route('ib39.cdr.finalization.review', $cdr) }}">
                        <i class="mdi mdi-check-all"></i>
                        <span>Submit as Final</span>
                    </a>
                @endif

                @if ($cdr->status === \App\Enums\Ib39CdrStatus::Completed && $cdr->currentFinalVersion)
                    <a class="hero-btn hero-btn-glass" href="{{ route('ib39.cdr.documents.preview', [$cdr, $cdr->currentFinalVersion]) }}">
                        <i class="mdi mdi-eye-outline"></i>
                        <span>View final copy</span>
                    </a>
                    @if ($cdr->currentFinalVersion->source_type === \App\Enums\Ib39CdrDocumentSource::Generated)
                        <a class="hero-btn hero-btn-glass" href="{{ route('ib39.cdr.documents.print', [$cdr, $cdr->currentFinalVersion]) }}" target="_blank" rel="noopener">
                            <i class="mdi mdi-printer"></i>
                            <span>Print final copy</span>
                        </a>
                    @else
                        <a class="hero-btn hero-btn-glass" href="{{ route('ib39.cdr.documents.download', [$cdr, $cdr->currentFinalVersion]) }}">
                            <i class="mdi mdi-download"></i>
                            <span>Download final copy</span>
                        </a>
                    @endif
                @else
                    <a class="hero-btn hero-btn-glass" href="{{ route('ib39.cdr.preview', $cdr) }}">
                        <i class="mdi mdi-eye-outline"></i>
                        <span>Preview draft</span>
                    </a>
                    <a class="hero-btn hero-btn-glass" href="{{ route('ib39.cdr.print', $cdr) }}" target="_blank" rel="noopener">
                        <i class="mdi mdi-printer"></i>
                        <span>Print draft</span>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <div class="row">
        {{-- Left Column: Document Details & Upload/Replace --}}
        <div class="col-xl-7">
            {{-- Document Details Panel --}}
            <section class="panel-card" aria-labelledby="details-heading">
                <div class="card-body">
                    <div class="section-heading">
                        <div class="section-icon">
                            <i class="mdi mdi-file-document-box-outline"></i>
                        </div>
                        <div>
                            <h3 id="details-heading" class="section-title">
                                {{ $cdr->status === \App\Enums\Ib39CdrStatus::Completed ? 'Final document details' : 'Draft details' }}
                            </h3>
                            <p class="section-subtitle">Schema, editor metadata, and revision timestamps</p>
                        </div>
                    </div>

                    <dl class="overview-grid mb-0">
                        <div class="overview-item">
                            <small><i class="mdi mdi-tag"></i> Schema version</small>
                            <strong>{{ $cdr->form?->schema_version ?? 1 }}</strong>
                        </div>
                        <div class="overview-item">
                            <small><i class="mdi mdi-clock-outline"></i> Last saved</small>
                            <strong>{{ $cdr->form?->updated_at?->format('F d, Y h:i A') ?? 'Not yet saved' }}</strong>
                        </div>
                        <div class="overview-item">
                            <small><i class="mdi mdi-account-edit"></i> Last editor</small>
                            <strong>{{ $cdr->form?->lastEditor?->name ?? 'Not available' }}</strong>
                        </div>
                        @if($cdr->status === \App\Enums\Ib39CdrStatus::Completed)
                            <div class="overview-item">
                                <small><i class="mdi mdi-tag-outline"></i> Final version</small>
                                <strong>v{{ $cdr->currentFinalVersion?->version_number }}</strong>
                            </div>
                            <div class="overview-item">
                                <small><i class="mdi mdi-calendar-check"></i> Finalized</small>
                                <strong>{{ $cdr->completed_at?->format('F d, Y h:i A') }}</strong>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>

            {{-- Upload Completed CDR Form --}}
            @can('uploadFinal', $cdr)
                <section class="panel-card" aria-labelledby="upload-heading">
                    <div class="card-body">
                        <div class="section-heading">
                            <div class="section-icon">
                                <i class="mdi mdi-cloud-upload"></i>
                            </div>
                            <div>
                                <h3 id="upload-heading" class="section-title">Upload Completed CDR</h3>
                                <p class="section-subtitle">Upload the signed custodial debriefing report file</p>
                            </div>
                        </div>

                        <div class="alert-amber">
                            <i class="mdi mdi-alert-circle-outline" aria-hidden="true"></i>
                            <div>
                                <strong>Notice:</strong> Successful upload will mark this CDR Completed and lock ordinary draft editing. The structured draft does not need to be complete.
                            </div>
                        </div>

                        <form method="POST" action="{{ route('ib39.cdr.documents.upload', $cdr) }}" enctype="multipart/form-data" id="uploadCdrForm">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="final_document" class="d-none">Final CDR file</label>
                                <div class="upload-zone-modern" onclick="document.getElementById('final_document').click()">
                                    <div class="upload-zone-icon">
                                        <i class="mdi mdi-cloud-upload"></i>
                                    </div>
                                    <div class="upload-zone-title" id="uploadDisplayTitle">Click to choose or drag Final CDR file</div>
                                    <p class="upload-zone-hint">PDF, JPEG, or PNG; maximum 20 MiB.</p>
                                    <input id="final_document" class="upload-zone-input @error('document') is-invalid @enderror" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required onchange="handleFileSelected(this, 'uploadDisplayTitle', 'uploadSelectedBadge')">
                                    <div id="uploadSelectedBadge" class="file-selected-badge">
                                        <i class="mdi mdi-file-check"></i>
                                        <span id="uploadSelectedText"></span>
                                    </div>
                                </div>
                                @error('document')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <label class="confirm-pill" for="upload_confirmed">
                                <input id="upload_confirmed" class="form-check-input" type="checkbox" name="confirmed" value="1" required>
                                <span>This is the final CDR.</span>
                            </label>
                            @error('confirmed')<div class="text-danger mb-2 font-weight-bold font-size-sm">{{ $message }}</div>@enderror

                            <div>
                                <button class="btn-submit-action" type="submit">
                                    <i class="mdi mdi-upload mr-1"></i>
                                    <span>Upload Completed CDR</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            @endcan

            {{-- Replace Final CDR Form --}}
            @can('replaceFinal', $cdr)
                <section class="panel-card" aria-labelledby="replace-heading">
                    <div class="card-body">
                        <div class="section-heading">
                            <div class="section-icon">
                                <i class="mdi mdi-file-replace"></i>
                            </div>
                            <div>
                                <h3 id="replace-heading" class="section-title">Replace Final CDR</h3>
                                <p class="section-subtitle">Upload a replacement version for this finalized record</p>
                            </div>
                        </div>

                        <p class="text-muted mb-3 font-size-sm">The current final remains active unless the replacement succeeds. Earlier versions are preserved.</p>

                        <form method="POST" action="{{ route('ib39.cdr.documents.replace', $cdr) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="replacement_document" class="d-none">Replacement file</label>
                                <div class="upload-zone-modern" onclick="document.getElementById('replacement_document').click()">
                                    <div class="upload-zone-icon">
                                        <i class="mdi mdi-file-replace"></i>
                                    </div>
                                    <div class="upload-zone-title" id="replaceDisplayTitle">Click to choose replacement file</div>
                                    <p class="upload-zone-hint">PDF, JPEG, or PNG; maximum 20 MiB.</p>
                                    <input id="replacement_document" class="upload-zone-input @error('document') is-invalid @enderror" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required onchange="handleFileSelected(this, 'replaceDisplayTitle', 'replaceSelectedBadge')">
                                    <div id="replaceSelectedBadge" class="file-selected-badge">
                                        <i class="mdi mdi-file-check"></i>
                                        <span id="replaceSelectedText"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="replacement_reason" class="font-weight-bold text-dark font-size-sm">Replacement reason</label>
                                <textarea id="replacement_reason" class="form-control @error('replacement_reason') is-invalid @enderror" name="replacement_reason" rows="3" maxlength="2000" placeholder="State reason for replacing the finalized document..." required>{{ old('replacement_reason') }}</textarea>
                                @error('replacement_reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <label class="confirm-pill" for="replacement_confirmed">
                                <input id="replacement_confirmed" class="form-check-input" type="checkbox" name="confirmed" value="1" required>
                                <span>I confirm this replacement final CDR.</span>
                            </label>

                            <div>
                                <button class="btn-submit-action" type="submit">
                                    <i class="mdi mdi-file-replace mr-1"></i>
                                    <span>Replace Final CDR</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            @endcan
        </div>

        {{-- Right Column: Final Document Version History --}}
        <div class="col-xl-5">
            @can('viewVersionHistory', $cdr)
                <section class="panel-card" aria-labelledby="history-heading">
                    <div class="card-body">
                        <div class="section-heading">
                            <div class="section-icon">
                                <i class="mdi mdi-history"></i>
                            </div>
                            <div>
                                <h3 id="history-heading" class="section-title">Final-document version history</h3>
                                <p class="section-subtitle">Audit trail of finalized document copies</p>
                            </div>
                        </div>

                        @if ($cdr->documentVersions->isEmpty())
                            <div class="empty-state-box">
                                <div class="empty-state-icon">
                                    <i class="mdi mdi-file-document-box-outline"></i>
                                </div>
                                <p class="mb-0 font-weight-bold text-dark">No final-document versions have been created.</p>
                                <small class="text-muted">Final copies will appear here upon submission or upload.</small>
                            </div>
                        @else
                            <div class="version-list">
                                @foreach ($cdr->documentVersions as $version)
                                    <article class="version-item-card">
                                        <div class="version-item-top">
                                            <div class="version-badge-pill">
                                                <span>v{{ $version->version_number }}</span>
                                                @if($cdr->current_final_version_id === $version->id)
                                                    <span class="version-tag-current">Current</span>
                                                @endif
                                            </div>
                                            <span class="version-source-tag">
                                                {{ $version->source_type === \App\Enums\Ib39CdrDocumentSource::Generated ? 'System Generated' : 'Direct Upload' }}
                                            </span>
                                        </div>
                                        <div class="version-filename">
                                            <i class="mdi mdi-file mr-1 text-muted"></i>
                                            {{ $version->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded ? $version->original_filename : 'System-authored CDR' }}
                                        </div>
                                        <div class="version-meta">
                                            <span><i class="mdi mdi-calendar"></i> {{ $version->finalized_at?->format('M d, Y · h:i A') }}</span>
                                            <span><i class="mdi mdi-account"></i> {{ $version->creator?->name ?? 'Unavailable' }}</span>
                                            <span><i class="mdi mdi-file-check"></i> {{ number_format($version->size_bytes / 1024, 1) }} KiB</span>
                                        </div>
                                        @if($version->replacesVersion)
                                            <div class="text-muted font-size-xs mt-1">
                                                <em>Replaces v{{ $version->replacesVersion->version_number }}: {{ $version->replacement_reason }}</em>
                                            </div>
                                        @endif
                                        <div class="version-actions">
                                            <a href="{{ route('ib39.cdr.documents.preview', [$cdr, $version]) }}" class="version-action-btn">
                                                <i class="mdi mdi-eye"></i> Preview
                                            </a>
                                            @if($version->source_type === \App\Enums\Ib39CdrDocumentSource::Uploaded)
                                                <a href="{{ route('ib39.cdr.documents.download', [$cdr, $version]) }}" class="version-action-btn">
                                                    <i class="mdi mdi-download"></i> Download
                                                </a>
                                            @else
                                                <a href="{{ route('ib39.cdr.documents.print', [$cdr, $version]) }}" target="_blank" rel="noopener" class="version-action-btn">
                                                    <i class="mdi mdi-printer"></i> Print
                                                </a>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleFileSelected(input, titleId, badgeId) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        document.getElementById(titleId).textContent = file.name;
        var badge = document.getElementById(badgeId);
        if (badge) {
            badge.style.display = 'inline-flex';
            var text = badge.querySelector('span');
            if (text) {
                text.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB · Ready to upload';
            }
        }
    }
}
</script>
@endpush
