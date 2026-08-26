@extends('layouts.skydash-v')
@section('title', 'PNP Dashboard')
@section('heading', 'PNP E-CLIP Workspace')

@push('styles')
<style>
    .pnp-page{--primary:#2f6fed;--navy:#172b4d;--border:#e7ecf3}.pnp-hero{background:linear-gradient(125deg,#173b74,#2f6fed);border-radius:18px;box-shadow:0 12px 30px rgba(47,111,237,.18);color:#fff;overflow:hidden;padding:1.8rem;position:relative}.pnp-hero::before,.pnp-hero::after{background:rgba(255,255,255,.07);border-radius:50%;content:'';position:absolute}.pnp-hero::before{height:240px;right:-70px;top:-120px;width:240px}.pnp-hero::after{bottom:-95px;height:170px;right:110px;width:170px}.pnp-eyebrow{font-size:.7rem;font-weight:700;letter-spacing:.11em;opacity:.76;text-transform:uppercase}.pnp-hero h2{color:#fff;font-size:1.7rem;font-weight:700}.pnp-hero p{font-size:.84rem;line-height:1.55;max-width:680px;opacity:.88}.workspace-badge{align-items:center;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.3);border-radius:18px;display:inline-flex;font-size:.7rem;font-weight:700;gap:.4rem;padding:.4rem .75rem;position:relative;z-index:1}.workspace-badge span{background:#ffd56a;border-radius:50%;box-shadow:0 0 0 4px rgba(255,213,106,.15);height:7px;width:7px}
    .pnp-card{border:1px solid var(--border);border-radius:14px;box-shadow:0 4px 16px rgba(23,43,77,.045)}.pnp-card .card-body{padding:1.4rem}.section-icon{align-items:center;background:#eaf1ff;border-radius:11px;color:#2f6fed;display:flex;flex:0 0 42px;font-size:1.2rem;height:42px;justify-content:center;margin-right:1.15rem!important;width:42px}.section-title{color:var(--navy);font-size:1rem;font-weight:700;margin:0 0 .2rem}.section-subtitle{color:#8492a6;font-size:.76rem;line-height:1.4;margin:0}.status-panel{align-items:flex-start;background:#fff9e9;border:1px solid #f1d995;border-radius:11px;color:#75570d;display:flex;gap:0;padding:.9rem}.status-panel>i{flex:0 0 auto;font-size:1.15rem;margin-right:.75rem;margin-top:.1rem}.status-panel strong{display:block;font-size:.8rem}.status-panel span{display:block;font-size:.73rem;line-height:1.45;margin-top:.15rem}.capability-list{list-style:none;margin:0;padding:0}.capability-item{align-items:flex-start;border-bottom:1px solid #edf1f6;display:flex;gap:0;padding:.9rem 0}.capability-item:first-child{padding-top:0}.capability-item:last-child{border-bottom:0;padding-bottom:0}.capability-icon{align-items:center;background:#f1f5fa;border-radius:9px;color:#637b9d;display:flex;flex:0 0 36px;font-size:1rem;height:36px;justify-content:center;margin-right:.8rem}.capability-item>div{min-width:0}.capability-item strong{color:#334155;display:block;font-size:.8rem}.capability-item small{color:#8492a6;display:block;font-size:.7rem;line-height:1.4;margin-top:.15rem}.pending-label{align-self:center;background:#f1f4f8;border-radius:12px;color:#64748b;display:inline-flex;font-size:.62rem;font-weight:700;margin-left:1rem;padding:.25rem .5rem;white-space:nowrap}.security-grid{display:grid;gap:.75rem;grid-template-columns:repeat(3,minmax(0,1fr))}.security-item{background:#f8fafc;border:1px solid #edf1f6;border-radius:10px;padding:.9rem}.security-item i{align-items:center;background:#eaf1ff;border-radius:8px;color:#2f6fed;display:flex;font-size:1rem;height:32px;justify-content:center;width:32px}.security-item strong{color:#334155;display:block;font-size:.76rem;margin:.55rem 0 .2rem}.security-item small{color:#8492a6;font-size:.68rem;line-height:1.4}.guidance{background:#eef5ff;border:1px solid #ccddfb;border-radius:11px;color:#36537c;font-size:.75rem;line-height:1.5;padding:.9rem}.guidance strong{align-items:center;color:#244a83;display:inline-flex;margin-bottom:.2rem}.guidance strong i{margin-right:.4rem!important}.empty-workspace{align-items:center;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:12px;color:#8492a6;display:flex;flex-direction:column;justify-content:center;min-height:220px;padding:2rem;text-align:center}.empty-workspace i{color:#b7c4d5;font-size:2.7rem;margin-bottom:.65rem}.empty-workspace strong{color:#42526b;font-size:.88rem}.empty-workspace span{font-size:.72rem;line-height:1.5;margin-top:.25rem;max-width:300px}
    @media(max-width:767px){.pnp-hero{padding:1.25rem}.pnp-hero h2{font-size:1.4rem}.workspace-badge{margin-top:1rem}.pnp-card .card-body{padding:1.1rem}.security-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="pnp-page">
    <x-shield.module-header
        eyebrow="Secure agency workspace"
        title="PNP E-CLIP Coordination"
        description="Process assigned firearms, explosives, and ammunition records within the authorized E-CLIP workflow."
        icon="mdi-shield-key-outline"
        role="PNP"
    >
        <x-slot:actions>
            <a class="btn btn-light btn-sm" href="{{ route('pnp.eclip-fea.index') }}"><i class="mdi mdi-folder-lock-open mr-1" aria-hidden="true"></i>Open FEA Queue</a>
        </x-slot:actions>
    </x-shield.module-header>

    <div class="row">
        <div class="col-xl-8">
            <section class="card pnp-card mb-4" aria-labelledby="workspace-status-title"><div class="card-body">
                <div class="d-flex align-items-center mb-4"><div class="section-icon mr-3"><i class="fa fa-shield"></i></div><div><h3 id="workspace-status-title" class="section-title">Workspace Status</h3><p class="section-subtitle">Current implementation state of the PNP module.</p></div></div>
                <div class="status-panel mb-4"><i class="fa fa-shield"></i><div><strong>FEA processing is available for assigned cases</strong><span>Use the FEA queue to upload authorized PTIS, TIR, CVIF, and related records. Other workflow actions remain restricted to their responsible offices.</span></div></div>
                <ul class="capability-list">
                    <li class="capability-item"><span class="capability-icon"><i class="fa fa-file-text"></i></span><div><strong>Secure Document Processing</strong><small>Private submission, authorized review, and controlled access to PNP-related E-CLIP documents.</small></div><span class="pending-label">Pending</span></li>
                    <li class="capability-item"><span class="capability-icon"><i class="fa fa-balance-scale"></i></span><div><strong>Surrendered-Firearm Valuation</strong><small>Structured valuation records based only on officially approved requirements and procedures.</small></div><span class="pending-label">Pending</span></li>
                    <li class="capability-item"><span class="capability-icon"><i class="fa fa-history"></i></span><div><strong>Review and Audit History</strong><small>Traceable actions, status changes, responsible personnel, and timestamps.</small></div><span class="pending-label">Pending</span></li>
                </ul>
            </div></section>

            <section class="card pnp-card mb-4" aria-labelledby="security-title"><div class="card-body">
                <div class="d-flex align-items-center mb-4"><div class="section-icon mr-3"><i class="fa fa-lock"></i></div><div><h3 id="security-title" class="section-title">Required Safeguards</h3><p class="section-subtitle">Controls that will remain mandatory when the workflow is enabled.</p></div></div>
                <div class="security-grid"><div class="security-item"><i class="fa fa-user-secret"></i><strong>Role-restricted access</strong><small>Only authorized personnel may view or act on assigned records.</small></div><div class="security-item"><i class="fa fa-folder-open"></i><strong>Private documents</strong><small>Every upload and download must remain validated and authorized.</small></div><div class="security-item"><i class="fa fa-list-alt"></i><strong>Complete traceability</strong><small>Material actions and decisions must be recorded in the audit history.</small></div></div>
            </div></section>
        </div>

        <div class="col-xl-4">
            <section class="card pnp-card mb-4" aria-labelledby="assigned-work-title"><div class="card-body"><div class="d-flex align-items-center mb-4"><div class="section-icon mr-3"><i class="fa fa-inbox"></i></div><div><h3 id="assigned-work-title" class="section-title">Assigned Work</h3><p class="section-subtitle">PNP cases requiring action.</p></div></div><div class="empty-workspace"><i class="fa fa-inbox"></i><strong>No actionable records</strong><span>Case assignments will appear only after the official PNP workflow has been defined and implemented.</span></div></div></section>
            <div class="guidance"><strong><i class="fa fa-info-circle mr-1"></i>Implementation guidance</strong><br>Do not use another module to process PNP records. The required workflow and authorization scope must be confirmed before development continues.</div>
        </div>
    </div>
</div>
@endsection
