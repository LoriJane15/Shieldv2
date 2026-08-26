@extends('layouts.skydash-v')
@section('title', 'Eligibility Review')
@section('heading', 'LSWDO Eligibility Review')

@push('styles')
<style>
    .review-page { --case-sticky-offset: 138px; --review-primary: #2f6fed; --review-navy: #172b4d; --review-border: #e7ecf3; }
    .review-back { align-items: center; color: #64748b; display: inline-flex; font-size: .82rem; font-weight: 600; gap: .4rem; margin-bottom: 1rem; }
    .review-back:hover { color: #2f6fed; text-decoration: none; }
    .case-hero { background: linear-gradient(125deg, #173b74, #2f6fed); border-radius: 16px; box-shadow: 0 10px 28px rgba(47, 111, 237, .16); color: #fff; overflow: hidden; padding: 1.5rem; position: relative; }
    .case-hero::after { background: rgba(255, 255, 255, .08); border-radius: 50%; content: ''; height: 180px; position: absolute; right: -45px; top: -90px; width: 180px; }
    .case-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .1em; opacity: .75; text-transform: uppercase; }
    .case-number { color: #fff; font-size: 1.6rem; font-weight: 700; }
    .case-status { background: rgba(255, 255, 255, .18); border: 1px solid rgba(255, 255, 255, .3); border-radius: 20px; color: #fff; display: inline-flex; font-size: .75rem; font-weight: 700; padding: .4rem .75rem; position: relative; z-index: 1; }
    .case-details { display: flex; flex-wrap: wrap; gap: .75rem 1rem; }
    .case-detail { align-items: center; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .1); border-radius: 10px; display: flex; gap: .65rem; min-height: 52px; padding: .5rem .7rem; }
    .case-detail > i { align-items: center; background: rgba(255, 255, 255, .12); border-radius: 8px; display: inline-flex; flex: 0 0 32px; font-size: 1.05rem; height: 32px; justify-content: center; opacity: .9; width: 32px; }
    .case-detail > div { min-width: 0; }
    .case-detail small { display: block; font-size: .67rem; opacity: .7; text-transform: uppercase; }
    .case-detail strong { display: block; font-size: .86rem; }
    .review-card { border: 1px solid var(--review-border); border-radius: 14px; box-shadow: 0 4px 16px rgba(23, 43, 77, .045); }
    .review-card .card-body { padding: 1.4rem; }
    .section-icon { align-items: center; background: #eaf1ff; border-radius: 10px; color: #2f6fed; display: flex; flex: 0 0 40px; height: 40px; justify-content: center; margin-right: .85rem !important; width: 40px; }
    .section-icon i { display: block; font-size: 1.25rem; line-height: 1; margin: 0; }
    .section-icon + div { min-width: 0; }
    .section-title { color: var(--review-navy); font-size: 1rem; font-weight: 700; margin: 0 0 .2rem; }
    .section-subtitle { color: #8492a6; font-size: .78rem; line-height: 1.45; margin: 0; }
    .service-link { align-items: center; background: #f7f9fc; border: 1px solid #e5eaf2; border-radius: 10px; color: #36537c; display: flex; font-size: .82rem; font-weight: 600; gap: .7rem; padding: .8rem 1rem; }
    .service-link:hover { background: #edf4ff; border-color: #b9cdf5; color: #245cc4; text-decoration: none; }
    .service-link i:first-child { align-items: center; background: #eaf1ff; border-radius: 8px; display: inline-flex; flex: 0 0 34px; font-size: 1rem; height: 34px; justify-content: center; line-height: 1; margin: 0; width: 34px; }
    .service-link i:last-child { margin-left: auto; }
    .decision-option { cursor: pointer; display: block; margin-bottom: .7rem; position: relative; }
    .decision-option input { opacity: 0; position: absolute; }
    .decision-content { align-items: center; border: 1px solid #dfe5ee; border-radius: 11px; display: flex; gap: .75rem; padding: .85rem; transition: border-color .15s, background-color .15s, box-shadow .15s; }
    .decision-content i { align-items: center; background: #f1f4f8; border-radius: 9px; color: #64748b; display: flex; flex: 0 0 36px; font-size: 1.05rem; height: 36px; justify-content: center; width: 36px; }
    .decision-content strong, .decision-content small { display: block; }
    .decision-content strong { color: #334155; font-size: .84rem; }
    .decision-content small { color: #8492a6; font-size: .72rem; }
    .decision-option input:focus + .decision-content { box-shadow: 0 0 0 3px rgba(47, 111, 237, .12); }
    .decision-option input:checked + .decision-content { background: #f1f6ff; border-color: #2f6fed; }
    .decision-option input:checked + .decision-content i { background: #2f6fed; color: #fff; }
    .decision-option.ineligible input:checked + .decision-content { background: #fff5f5; border-color: #e65b65; }
    .decision-option.ineligible input:checked + .decision-content i { background: #e65b65; }
    .decision-option.returned input:checked + .decision-content { background: #fff9e9; border-color: #d89400; }
    .decision-option.returned input:checked + .decision-content i { background: #d89400; }
    .remarks-label { color: #42526b; font-size: .8rem; font-weight: 600; }
    .remarks-help { color: #8492a6; font-size: .72rem; font-weight: 400; }
    .review-submit { border-radius: 9px; font-weight: 600; padding: .65rem 1rem; }
    .checklist-progress { color: #64748b; font-size: .78rem; }
    .document-item { border: 1px solid #e7ecf3; border-radius: 12px; margin-bottom: 1rem; overflow: hidden; }
    .document-item:last-child { margin-bottom: 0; }
    .document-heading { align-items: center; background: #f8fafc; border-bottom: 1px solid #edf1f6; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; padding: .85rem 1rem; }
    .requirement-name { color: #334155; font-size: .86rem; font-weight: 700; }
    .required-mark { color: #dc3545; }
    .document-status { border-radius: 14px; display: inline-flex; font-size: .7rem; font-weight: 700; padding: .3rem .65rem; }
    .status-missing { background: #f1f4f8; color: #64748b; }
    .status-pending { background: #fff4d6; color: #9a6800; }
    .status-certified, .status-approved { background: #e6f7ef; color: #16845e; }
    .status-rejected, .status-incomplete { background: #ffebed; color: #bd3e49; }
    .document-body { padding: 1rem; }
    .version-grid { display: grid; gap: .55rem; }
    .document-preview-link { align-items: center; border: 1px solid #dfe5ee; border-radius: 9px; color: #36537c; display: flex; gap: .65rem; padding: .65rem .75rem; text-decoration: none; transition: border-color .15s, background-color .15s, transform .15s; }
    .document-preview-link:hover { background: #f3f7ff; border-color: #afc5ef; color: #245cc4; text-decoration: none; transform: translateY(-1px); }
    .document-preview-link i { align-items: center; background: #eaf1ff; border-radius: 8px; color: #2f6fed; display: flex; flex: 0 0 34px; height: 34px; justify-content: center; width: 34px; }
    .document-preview-link strong, .document-preview-link small { display: block; }
    .document-preview-link strong { font-size: .78rem; }
    .document-preview-link small { color: #8492a6; font-size: .7rem; max-width: 210px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .latest-badge { background: #e6f7ef; border-radius: 10px; color: #16845e; font-size: .6rem; margin-left: .3rem; padding: .18rem .4rem; }
    .no-document { align-items: center; background: #f8fafc; border: 1px dashed #d8e0ea; border-radius: 9px; color: #8492a6; display: flex; font-size: .78rem; gap: .5rem; justify-content: center; min-height: 58px; padding: .75rem; }
    .supporting-upload { background: #f8fafc; border: 1px dashed #aebbd0; border-radius: 10px; padding: .8rem; transition: border-color .2s, background-color .2s; }
    .supporting-upload:focus-within { background: #f2f6ff; border-color: #2f6fed; box-shadow: 0 0 0 3px rgba(47, 111, 237, .1); }
    .supporting-upload-picker { align-items: center; cursor: pointer; display: flex; gap: .65rem; margin-bottom: .55rem; }
    .supporting-upload-icon { align-items: center; background: #eaf1ff; border-radius: 8px; color: #2f6fed; display: inline-flex; flex: 0 0 36px; height: 36px; justify-content: center; width: 36px; }
    .supporting-upload-title { color: #334155; display: block; font-size: .8rem; font-weight: 600; }
    .supporting-upload-help, .supporting-upload-name { display: block; font-size: .7rem; }
    .supporting-upload-name { color: #64748b; margin-bottom: .55rem; overflow-wrap: anywhere; }
    .supporting-upload input[type=file] { border: 0; clip: rect(0, 0, 0, 0); height: 1px; overflow: hidden; padding: 0; position: absolute; white-space: nowrap; width: 1px; }
    .history-timeline { list-style: none; margin: 0; padding: 0; }
    .history-item { padding: 0 0 1.25rem 2rem; position: relative; }
    .history-item:last-child { padding-bottom: 0; }
    .history-item::before { background: #dce5f2; bottom: 0; content: ''; left: .43rem; position: absolute; top: 14px; width: 2px; }
    .history-item:last-child::before { display: none; }
    .history-dot { background: #fff; border: 3px solid #2f6fed; border-radius: 50%; height: 15px; left: 0; position: absolute; top: 3px; width: 15px; }
    .history-status { color: #334155; font-size: .83rem; font-weight: 700; line-height: 1.4; }
    .history-meta { align-items: center; color: #8492a6; display: flex; flex-wrap: wrap; font-size: .7rem; gap: .3rem; line-height: 1.5; margin-top: .3rem; }
    .history-meta i { flex: 0 0 auto; font-size: .85rem; line-height: 1; margin: 0; }
    .history-remarks { background: #f8fafc; border-radius: 8px; color: #64748b; font-size: .76rem; margin-top: .45rem; padding: .6rem .7rem; }
    .document-checklist-alert { align-items: center; display: flex; gap: .75rem; line-height: 1.45; }
    .document-checklist-alert i { flex: 0 0 auto; font-size: 1.2rem; line-height: 1; margin: 0 !important; }
    .workflow-overview { align-items: center; background: #f7f9fc; border: 1px solid #e5eaf2; border-radius: 12px; display: flex; gap: 1rem; margin-bottom: 1rem; padding: .9rem 1rem; }
    .workflow-progress { background: #dfe6f0; border-radius: 999px; flex: 1; height: 8px; overflow: hidden; }
    .workflow-progress-bar { background: linear-gradient(90deg, #2f6fed, #20a779); height: 100%; transition: width .25s ease; }
    .workflow-count { color: #42526b; font-size: .76rem; font-weight: 700; white-space: nowrap; }
    .phase-nav { display: grid; gap: .5rem; grid-template-columns: repeat(5, minmax(0, 1fr)); margin-bottom: 1rem; position: relative; }
    .phase-tab { align-items: center; background: #fff; border: 1px solid #e1e7f0; border-radius: 11px; color: #64748b; cursor: pointer; display: flex; gap: .55rem; min-height: 66px; padding: .65rem; text-align: left; transition: border-color .15s, box-shadow .15s, transform .15s; }
    .phase-tab:hover { border-color: #afc5ef; transform: translateY(-1px); }
    .phase-tab:focus { box-shadow: 0 0 0 3px rgba(47, 111, 237, .12); outline: 0; }
    .phase-tab[aria-selected="true"] { background: #f2f6ff; border-color: #2f6fed; color: #245cc4; box-shadow: 0 4px 12px rgba(47, 111, 237, .1); }
    .phase-tab.is-complete { border-color: #bfe7d7; }
    .phase-tab.is-alert { border-color: #efadb3; }
    .phase-tab.is-current:not(.is-alert) { border-color: #8eafea; }
    .phase-number { align-items: center; background: #edf1f6; border-radius: 9px; color: #64748b; display: flex; flex: 0 0 30px; font-size: .75rem; font-weight: 800; height: 30px; justify-content: center; }
    .phase-tab[aria-selected="true"] .phase-number { background: #2f6fed; color: #fff; }
    .phase-tab.is-complete .phase-number { background: #def5eb; color: #16845e; }
    .phase-tab-copy { min-width: 0; }
    .phase-tab-heading { align-items: center; display: flex; gap: .35rem; }
    .phase-tab-title { display: block; font-size: .72rem; font-weight: 800; line-height: 1.2; }
    .phase-tab-meta { display: block; font-size: .62rem; margin-top: .2rem; opacity: .8; }
    .phase-live { align-items: center; display: inline-flex; flex: 0 0 auto; height: 14px; justify-content: center; width: 14px; }
    .phase-live-dot { background: #20a779; border: 2px solid #fff; border-radius: 50%; box-shadow: 0 0 0 0 rgba(32, 167, 121, .5); display: inline-block; height: 9px; width: 9px; animation: phase-live-pulse 1.5s ease-out infinite; }
    @keyframes phase-live-pulse { 0% { box-shadow: 0 0 0 0 rgba(32, 167, 121, .5); opacity: 1; } 70% { box-shadow: 0 0 0 6px rgba(32, 167, 121, 0); opacity: .65; } 100% { box-shadow: 0 0 0 0 rgba(32, 167, 121, 0); opacity: 1; } }
    .phase-panel { border: 1px solid #e5eaf2; border-radius: 12px; overflow: hidden; }
    .phase-panel[hidden] { display: none; }
    .phase-panel-header { align-items: center; background: linear-gradient(100deg, #f8faff, #fff); border-bottom: 1px solid #e8edf4; display: flex; justify-content: space-between; padding: 1rem 1.1rem; }
    .phase-panel-title { color: #263b5e; font-size: .95rem; font-weight: 800; margin: 0; }
    .phase-panel-description { color: #64748b; font-size: .74rem; margin: .25rem 0 0; }
    .phase-panel-meta { color: #8492a6; font-size: .68rem; margin: .2rem 0 0; }
    .phase-controls { display: flex; gap: .4rem; }
    .phase-control { align-items: center; background: #fff; border: 1px solid #dfe5ee; border-radius: 8px; color: #52657f; cursor: pointer; display: inline-flex; font-size: .7rem; font-weight: 700; gap: .25rem; padding: .4rem .6rem; }
    .phase-control:disabled { cursor: not-allowed; opacity: .4; }
    .phase-steps { background: #fff; padding: .65rem 1.1rem; }
    .official-step { align-items: flex-start; display: flex; gap: .85rem; padding: .7rem 0 1rem; position: relative; }
    .official-step:not(:last-child)::after { background: #dfe6f0; content: ''; left: 17px; position: absolute; top: 43px; bottom: -1px; width: 2px; }
    .step-marker { align-items: center; background: #edf1f6; border: 3px solid #fff; border-radius: 50%; box-shadow: 0 0 0 1px #dfe6f0; color: #718096; display: flex; flex: 0 0 36px; font-size: .68rem; font-weight: 800; height: 36px; justify-content: center; position: relative; z-index: 1; }
    .status-completed .step-marker, .status-not-applicable .step-marker { background: #def5eb; color: #16845e; }
    .status-ongoing .step-marker { background: #e1ebff; color: #2f6fed; }
    .status-late .step-marker, .status-returned-for-correction .step-marker { background: #ffebed; color: #bd3e49; }
    .status-locked .step-marker { background: #f1f3f6; color: #a0aaba; }
    .step-content { flex: 1; min-width: 0; }
    .step-name { color: #334155; display: block; font-size: .82rem; font-weight: 700; line-height: 1.35; padding-top: .12rem; }
    .step-meta { align-items: center; color: #8492a6; display: flex; flex-wrap: wrap; font-size: .68rem; gap: .45rem; margin-top: .25rem; }
    .step-badge { border-radius: 12px; display: inline-flex; font-size: .62rem; font-weight: 800; letter-spacing: .02em; padding: .22rem .5rem; text-transform: uppercase; }
    .step-badge-completed, .step-badge-not-applicable { background: #def5eb; color: #16845e; }
    .step-badge-ongoing { background: #e1ebff; color: #2f6fed; }
    .step-badge-late, .step-badge-returned-for-correction { background: #ffebed; color: #bd3e49; }
    .step-badge-pending { background: #fff3cd; color: #8a6500; }
    .step-badge-locked { background: #edf1f6; color: #718096; }
    .step-action-button { align-items: center; background: #f2f6ff; border: 1px solid #c9d9f5; border-radius: 8px; color: #2f6fed; display: inline-flex; flex: 0 0 auto; font-size: .7rem; font-weight: 800; gap: .3rem; padding: .4rem .65rem; }
    .step-action-button:hover { background: #2f6fed; color: #fff; }
    .step-modal .modal-dialog { margin: 1.75rem auto; max-width: 740px; }
    .step-modal .modal-content { border: 1px solid #dfe6f0; border-radius: 14px; box-shadow: 0 20px 50px rgba(23, 43, 77, .2); }
    .step-modal .modal-header { align-items: flex-start; background: #173b74; border: 0; color: #fff; height: auto !important; min-height: 76px; overflow: visible; padding: 1.05rem 1.25rem; }
    .step-modal .modal-header > div { min-width: 0; padding-right: 1rem; }
    .step-modal .modal-title { color: #fff; font-size: .95rem; font-weight: 700; line-height: 1.35; margin: 0; }
    .step-modal-kicker { display: block; font-size: .65rem; font-weight: 800; letter-spacing: .08em; margin-bottom: .25rem; opacity: .72; text-transform: uppercase; }
    .step-modal .btn-close { flex: 0 0 auto; margin: -.25rem -.25rem -.25rem auto; opacity: .8; position: static; }
    .step-modal .btn-close:hover { opacity: 1; }
    .step-modal .modal-body { background: #fff; color: #334155; flex: 1 1 auto; overflow-y: auto; padding: 1.2rem 1.25rem; }
    .step-modal .modal-footer { background: #f8fafc; border-top: 1px solid #e6ebf2; flex: 0 0 auto; padding: .8rem 1.25rem; }
    .step-action-form { display: flex; flex-direction: column; width: 100%; }
    .step-form-heading { color: #263b5e; font-size: .84rem; font-weight: 800; line-height: 1.35; margin: 0 0 .25rem; }
    .step-form-help { color: #64748b; font-size: .72rem; line-height: 1.45; margin: 0 0 .9rem; }
    .step-status-options { display: grid; gap: .55rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .step-status-option { cursor: pointer; margin: 0; position: relative; }
    .step-status-option input { height: 1px; opacity: 0; position: absolute; width: 1px; }
    .step-status-choice { align-items: center; background: #f8fafc; border: 1px solid #dfe5ee; border-radius: 10px; display: flex; gap: .65rem; min-height: 54px; padding: .65rem .75rem; transition: border-color .15s, background-color .15s, box-shadow .15s; }
    .step-status-choice i { align-items: center; background: #edf1f6; border-radius: 7px; color: #64748b; display: flex; flex: 0 0 30px; height: 30px; justify-content: center; }
    .step-status-choice > span { min-width: 0; }
    .step-status-choice strong { color: #263b5e; display: block; font-size: .76rem; line-height: 1.35; }
    .step-status-choice small { color: #64748b; display: block; font-size: .67rem; line-height: 1.35; margin-top: .1rem; }
    .step-status-option input:focus + .step-status-choice { box-shadow: 0 0 0 3px rgba(47, 111, 237, .12); }
    .step-status-option input:checked + .step-status-choice { background: #edf4ff; border-color: #2f6fed; }
    .step-status-option input:checked + .step-status-choice i { background: #2f6fed; color: #fff; }
    .step-status-option.choice-complete input:checked + .step-status-choice { background: #edf9f4; border-color: #20a779; }
    .step-status-option.choice-complete input:checked + .step-status-choice i { background: #20a779; }
    .step-status-option.choice-return input:checked + .step-status-choice { background: #fff3f4; border-color: #dc5965; }
    .step-status-option.choice-return input:checked + .step-status-choice i { background: #dc5965; }
    .step-input-label { color: #334155; display: flex; font-size: .74rem; font-weight: 800; gap: .75rem; justify-content: space-between; line-height: 1.35; margin: .9rem 0 .4rem; }
    .step-input-label span { color: #64748b; font-size: .65rem; font-weight: 500; text-align: right; }
    .step-remarks-input { border-color: #d8e0ea; border-radius: 9px; font-size: .75rem; min-height: 82px; resize: vertical; }
    .step-required-docs { background: #f8fafc; border: 1px solid #e3e8f0; border-radius: 9px; margin-top: .8rem; padding: .7rem .8rem; }
    .step-required-docs-title { color: #42526b; display: block; font-size: .68rem; font-weight: 800; line-height: 1.35; margin-bottom: .4rem; text-transform: uppercase; }
    .step-required-doc { align-items: flex-start; color: #52657f; display: flex; font-size: .7rem; gap: .45rem; line-height: 1.4; margin-top: .3rem; }
    .step-required-doc i { color: #2f6fed; }
    .step-form-note { color: #64748b; font-size: .67rem; line-height: 1.35; margin-right: auto; }
    .step-save { align-items: center; border-radius: 8px; display: inline-flex; font-size: .72rem; font-weight: 700; justify-content: center; min-height: 36px; padding: .45rem .8rem; white-space: nowrap; }
    .step-remarks { color: #64748b; font-size: .7rem; margin-top: .35rem; }
    .case-workspace { align-items: start; display: grid; gap: 1.25rem; grid-template-columns: minmax(0, 1.85fr) minmax(320px, 1fr); }
    .case-main, .case-sidebar { min-width: 0; }
    .case-sidebar { max-height: calc(100vh - var(--case-sticky-offset) - 1rem); overflow-y: auto; padding-right: .25rem; position: sticky; top: var(--case-sticky-offset); }
    .case-sidebar::-webkit-scrollbar { width: 6px; }
    .case-sidebar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .workflow-tools { align-items: center; display: flex; gap: .4rem; justify-content: flex-end; margin: -.2rem 0 .75rem; }
    .workflow-tools-label { color: #8492a6; font-size: .68rem; font-weight: 700; margin-right: .2rem; }
    .step-filter { background: #fff; border: 1px solid #dfe5ee; border-radius: 999px; color: #64748b; font-size: .67rem; font-weight: 700; padding: .3rem .65rem; }
    .step-filter.is-active { background: #173b74; border-color: #173b74; color: #fff; }
    .phase-nav { background: #fff; padding: .35rem 0 .65rem; position: sticky; top: var(--case-sticky-offset); z-index: 8; }
    .phase-steps { display: grid; gap: .5rem; padding: .75rem; }
    .official-step { background: #fff; border: 1px solid #e4e9f1; border-radius: 10px; display: block; padding: 0; }
    .official-step[open] { border-color: #b9cdf5; box-shadow: 0 3px 10px rgba(23, 43, 77, .05); }
    .official-step.status-pending[open], .official-step.status-late[open], .official-step.status-returned-for-correction[open] { background: #fffaf0; border-color: #e8c66b; }
    .official-step:not(:last-child)::after { display: none; }
    .step-summary { align-items: center; cursor: pointer; display: flex; gap: .75rem; list-style: none; min-height: 58px; padding: .65rem .75rem; }
    .step-summary::-webkit-details-marker { display: none; }
    .step-summary:focus-visible { border-radius: 10px; box-shadow: 0 0 0 3px rgba(47, 111, 237, .16); outline: 0; }
    .step-summary .step-marker { border-width: 2px; flex-basis: 32px; height: 32px; width: 32px; }
    .step-chevron { color: #8492a6; flex: 0 0 auto; transition: transform .15s ease; }
    .official-step[open] .step-chevron { transform: rotate(180deg); }
    .step-expanded { border-top: 1px solid #edf1f6; padding: .7rem .8rem .8rem 3.95rem; }
    .step-expanded .step-action-button { margin-top: .65rem; }
    .step-inline-documents { color: #64748b; display: grid; font-size: .7rem; gap: .2rem; line-height: 1.45; }
    .step-inline-documents strong { color: #42526b; font-size: .67rem; text-transform: uppercase; }
    .step-empty-detail { color: #8492a6; font-size: .7rem; margin: 0; }
    [data-step-item][hidden] { display: none; }
    .review-page { margin: 0 auto; max-width: 1560px; }
    .case-status-label { display: block; font-size: .58rem; font-weight: 700; margin-right: .4rem; opacity: .72; text-transform: uppercase; }
    .case-last-updated { align-items: center; display: flex; font-size: .68rem; gap: .35rem; margin-top: .65rem; opacity: .76; }
    .case-context { align-items: center; background: #fff; border: 1px solid #dfe5ee; border-radius: 10px; box-shadow: 0 4px 14px rgba(23,43,77,.08); display: flex; gap: 1rem; justify-content: space-between; margin-bottom: 1rem; min-height: 48px; padding: .55rem .8rem; position: static; }
    .context-case { color: #263b5e; font-size: .78rem; font-weight: 800; white-space: nowrap; }
    .context-phase { color: #64748b; flex: 1; font-size: .7rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .context-progress { color: #52657f; font-size: .68rem; font-weight: 700; white-space: nowrap; }
    .workflow-overview { display: block; padding: .85rem 1rem; }
    .workflow-progress-heading { align-items: center; display: flex; justify-content: space-between; margin-bottom: .6rem; }
    .workflow-progress-heading strong { color: #334155; font-size: .78rem; }
    .workflow-progress-heading span { color: #5746af; font-size: .86rem; font-weight: 800; }
    .workflow-progress { height: 9px; }
    .workflow-progress-bar { background: linear-gradient(90deg, #6554c0, #3476d9); }
    .workflow-breakdown { display: flex; flex-wrap: wrap; gap: .45rem 1rem; margin-top: .55rem; }
    .workflow-breakdown span { align-items: center; color: #64748b; display: inline-flex; font-size: .68rem; gap: .35rem; }
    .workflow-breakdown i { border-radius: 50%; display: inline-block; height: 7px; width: 7px; }
    .count-complete i { background: #20a779; }.count-pending i { background: #d89a17; }.count-locked i { background: #a9b4c2; }
    .phase-nav { position: relative; top: auto; z-index: 1; }
    .phase-tab { min-height: 74px; }
    .phase-tab.is-complete { background: #f4fbf8; border-color: #bfe7d7; color: #28775d; }
    .phase-tab.is-current, .phase-tab[aria-selected="true"] { background: #f2f4ff; border-color: #6554c0; box-shadow: 0 3px 10px rgba(101,84,192,.1); color: #5141a5; }
    .phase-tab.is-future { background: #fafbfc; color: #8a98aa; }
    .phase-tab.is-attention { background: #fff9eb; border-color: #e7c671; color: #8a6500; }
    .phase-state { display: block; font-size: .58rem; font-weight: 800; letter-spacing: .04em; margin-top: .2rem; text-transform: uppercase; }
    .next-action-card { background: linear-gradient(100deg, #f5f3ff, #fff); border: 1px solid #cec7ef; border-left: 4px solid #6554c0; border-radius: 11px; margin: .9rem 0 1rem; padding: 1rem; }
    .next-action-kicker { color: #6554c0; font-size: .62rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .next-action-layout { align-items: center; display: flex; gap: 1rem; justify-content: space-between; margin-top: .45rem; }
    .next-action-copy { min-width: 0; }
    .next-action-title { color: #263b5e; font-size: .9rem; font-weight: 800; line-height: 1.35; margin: 0; }
    .next-action-meta { align-items: center; display: flex; flex-wrap: wrap; gap: .45rem .8rem; margin-top: .45rem; }
    .next-action-help { color: #64748b; font-size: .7rem; line-height: 1.45; margin: .45rem 0 0; }
    .next-action-button { align-items: center; background: #6554c0; border: 1px solid #6554c0; border-radius: 8px; color: #fff; display: inline-flex; flex: 0 0 auto; font-size: .7rem; font-weight: 750; gap: .35rem; justify-content: center; min-height: 40px; padding: .5rem .8rem; }
    .next-action-button:hover, .next-action-button:focus { background: #5545ad; color: #fff; outline: 3px solid rgba(101,84,192,.14); text-decoration: none; }
    .next-action-empty { align-items: center; display: flex; gap: .65rem; }
    .next-action-empty i { color: #20a779; font-size: 1.35rem; }
    .next-action-empty strong { color: #334155; display: block; font-size: .82rem; }.next-action-empty span { color: #718096; display: block; font-size: .7rem; margin-top: .15rem; }
    .workflow-status-badge { align-items: center; border-radius: 13px; display: inline-flex; font-size: .62rem; font-weight: 800; line-height: 1.2; min-height: 24px; padding: .28rem .55rem; text-transform: uppercase; }
    .workflow-status-complete { background: #def5eb; color: #16845e; }.workflow-status-active { background: #e7efff; color: #2f6fed; }.workflow-status-pending { background: #fff3d6; color: #8a6500; }.workflow-status-attention { background: #fff0df; color: #a45f0c; }.workflow-status-rejected { background: #ffebed; color: #bd3e49; }.workflow-status-locked { background: #edf1f6; color: #718096; }
    .responsible-agency { align-items: center; color: #596c84; display: inline-flex; font-size: .68rem; font-weight: 600; gap: .3rem; }
    .responsible-agency i { color: #8193aa; font-size: .8rem; }
    .phase-panel-header { padding: .8rem 1rem; }
    .phase-control { min-height: 36px; }
    .phase-steps { gap: .35rem; padding: .6rem; }
    .official-step { border-radius: 8px; }
    .official-step.is-next-step { border-color: #a99ee1; box-shadow: 0 2px 8px rgba(101,84,192,.08); }
    .official-step.status-locked { background: #fafbfc; }
    .step-summary { min-height: 50px; padding: .48rem .65rem; }
    .step-summary .step-marker { flex-basis: 28px; height: 28px; width: 28px; }
    .step-name { font-size: .76rem; }
    .step-meta { font-size: .64rem; margin-top: .18rem; }
    .step-lock-reason { color: #8794a6; font-size: .64rem; }
    .step-expanded { padding: .65rem .75rem .7rem 3.35rem; }
    .step-detail-grid { display: grid; gap: .65rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .step-detail { background: #f8fafc; border-radius: 7px; padding: .55rem .65rem; }
    .step-detail-label { color: #8794a6; display: block; font-size: .58rem; font-weight: 800; margin-bottom: .2rem; text-transform: uppercase; }
    .step-detail-value { color: #45566d; font-size: .68rem; line-height: 1.4; }
    .step-dependencies { list-style: none; margin: .25rem 0 0; padding: 0; }.step-dependencies li { align-items: flex-start; color: #64748b; display: flex; font-size: .67rem; gap: .35rem; margin-top: .25rem; }.step-dependencies i { color: #9aa8b8; margin-top: .1rem; }
    .case-sidebar { max-height: none; overflow: visible; padding-right: 0; top: 78px; }
    .sidebar-card-header { align-items: center; display: flex; gap: .65rem; margin-bottom: .8rem; }
    .sidebar-card-header .section-icon { flex-basis: 34px; height: 34px; width: 34px; }
    .sidebar-card-header .section-title { font-size: .88rem; }
    .sidebar-card-header .section-subtitle { font-size: .68rem; }
    .sidebar-count { background: #f0edff; border-radius: 12px; color: #5d4eb0; font-size: .64rem; font-weight: 800; margin-left: auto; padding: .25rem .5rem; white-space: nowrap; }
    .sidebar-alert { align-items: flex-start; background: #fff9e9; border: 1px solid #f0dfaa; border-radius: 8px; color: #725c1b; display: flex; font-size: .68rem; gap: .5rem; line-height: 1.45; padding: .65rem; }
    .sidebar-alert i { flex: 0 0 auto; font-size: .9rem; }
    .sidebar-disclosure { border-top: 1px solid #edf1f6; margin-top: .75rem; padding-top: .65rem; }
    .sidebar-disclosure > summary { align-items: center; color: #50627a; cursor: pointer; display: flex; font-size: .7rem; font-weight: 700; justify-content: space-between; list-style: none; min-height: 36px; }
    .sidebar-disclosure > summary::-webkit-details-marker { display: none; }.sidebar-disclosure[open] > summary i { transform: rotate(180deg); }.sidebar-disclosure > summary i { transition: transform .15s; }
    .sidebar-document-list { display: grid; gap: .4rem; margin-top: .55rem; }
    .sidebar-document-item { border: 1px solid #e6ebf2; border-radius: 8px; padding: .55rem; }
    .sidebar-document-heading { align-items: center; display: flex; gap: .4rem; justify-content: space-between; }.sidebar-document-heading strong { color: #3f5067; font-size: .68rem; line-height: 1.35; }
    .sidebar-upload { margin-top: .5rem; }
    .sidebar-history-more { border-top: 1px solid #edf1f6; margin-top: .7rem; padding-top: .55rem; }
    .sidebar-history-more summary { color: #6554c0; cursor: pointer; font-size: .68rem; font-weight: 700; list-style: none; }
    @media (min-width: 992px) {
        .case-hero { padding: 1.25rem 1.4rem; position: relative; top: auto; z-index: 1; }
        .case-number { font-size: 1.65rem; }
        .case-status { margin-left: .75rem; white-space: nowrap; }
    }
    @media (prefers-reduced-motion: reduce) { .phase-live-dot { animation: none; } }
    @media (max-width: 991px) { .phase-nav { display: flex; margin-left: -1px; margin-right: -1px; overflow-x: auto; padding: 1px 1px .4rem; scroll-snap-type: x mandatory; } .phase-tab { flex: 0 0 155px; scroll-snap-align: start; } }
    @media (max-width: 991px) { .case-workspace { display: block; } .case-sidebar { max-height: none; overflow: visible; padding-right: 0; position: static; } .case-context { position: static; } }
    @media (max-width: 767px) { .case-hero { padding: 1.2rem; } .case-number { font-size: 1.35rem; } .case-status { margin-left: 0; margin-top: .75rem; } .case-context { align-items: flex-start; flex-direction: column; gap: .25rem; } .context-phase { white-space: normal; } .review-card .card-body { padding: 1rem; } .workflow-overview { align-items: stretch; flex-direction: column; gap: .55rem; } .workflow-tools { justify-content: flex-start; } .next-action-layout { align-items: flex-start; flex-direction: column; }.next-action-button { width: 100%; }.phase-panel-header { align-items: flex-start; flex-direction: column; gap: .75rem; } .phase-controls { width: 100%; }.phase-control { flex: 1; justify-content: center; }.step-expanded { padding-left: .7rem; }.step-detail-grid { grid-template-columns: 1fr; }.step-status-options { grid-template-columns: 1fr; } .step-modal .modal-dialog { margin: .5rem; } .step-modal .modal-footer { align-items: stretch; flex-direction: column-reverse; gap: .6rem; } .step-save { width: 100%; } }
</style>
@endpush

@section('content')
@php
    $documentsByRequirement = $case->documents->keyBy('requirement_id');
    $submittedDocuments = $requirements->filter(fn ($requirement) => $documentsByRequirement->has($requirement->id))->count();
    $workflowActivities = $workflow['activities'];
    $activePhase = $workflow['current_phase'];
    $currentPhaseData = $workflow['phases']->get($activePhase);
@endphp

<div class="review-page">
    <a href="{{ route('lswdo.eclip.index') }}" class="review-back"><i class="mdi mdi-arrow-left"></i> Back to eligibility cases</a>

    <section class="case-hero mb-4" aria-labelledby="case-number">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start position-relative" style="z-index: 1;">
            <div class="shield-hero-primary">
                <span class="shield-title-icon"><i class="mdi mdi-folder-account-outline" aria-hidden="true"></i></span>
                <div>
                    <div class="case-eyebrow mb-1">E-CLIP Case</div>
                    <h2 id="case-number" class="case-number mb-3">{{ $case->case_number }}</h2>
                    <div class="case-details">
                        <div class="case-detail"><i class="mdi mdi-account-key-outline"></i><div><small>Beneficiary</small><strong>{{ $case->formerRebel->classified_id }}</strong></div></div>
                        <div class="case-detail"><i class="mdi mdi-map-marker-outline"></i><div><small>Municipality</small><strong>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</strong></div></div>
                        <div class="case-detail"><i class="mdi mdi-calendar-check-outline"></i><div><small>Submitted</small><strong>{{ $case->submitted_at?->format('M d, Y') ?? 'Not recorded' }}</strong></div></div>
                    </div>
                    @if($workflow['last_updated'])<div class="case-last-updated"><i class="mdi mdi-update" aria-hidden="true"></i>Last updated <time datetime="{{ $workflow['last_updated']->toIso8601String() }}">{{ $workflow['last_updated']->format('M d, Y · h:i A') }}</time></div>@endif
                </div>
            </div>
            <span class="case-status"><span class="case-status-label">Eligibility status</span><i class="mdi mdi-progress-check mr-1"></i>{{ $case->status->label() }}</span>
        </div>
    </section>

    @if($currentPhaseData)
        <div class="case-context" aria-label="Current workflow context">
            <span class="context-case">{{ $case->case_number }}</span>
            <span class="context-phase">Phase {{ $activePhase }} · {{ $currentPhaseData['name'] }}</span>
            <span class="context-progress">{{ $currentPhaseData['finished'] }} / {{ $currentPhaseData['total'] }} completed</span>
        </div>
    @endif

    <div class="case-workspace">
        <main class="case-main">
    @if($workflowActivities->isNotEmpty())
        <section class="card review-card mb-4" aria-labelledby="official-workflow-title">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="section-icon mr-3"><i class="mdi mdi-timeline-text-outline" aria-hidden="true"></i></div>
                    <div><h3 id="official-workflow-title" class="section-title">Official E-CLIP and Amnesty Workflow</h3><p class="section-subtitle">Phases and activities prescribed by the approved program flow.</p></div>
                </div>
                <div class="workflow-overview" aria-label="Overall E-CLIP workflow progress: {{ $workflow['percent'] }} percent">
                    <div class="workflow-progress-heading"><strong>Overall E-CLIP Progress</strong><span>{{ $workflow['percent'] }}%</span></div>
                    <div class="workflow-progress" role="progressbar" aria-valuenow="{{ $workflow['percent'] }}" aria-valuemin="0" aria-valuemax="100"><div class="workflow-progress-bar" style="width: {{ $workflow['percent'] }}%"></div></div>
                    <div class="workflow-breakdown"><span class="count-complete"><i></i>{{ $workflow['counts']['completed'] }} completed</span><span class="count-pending"><i></i>{{ $workflow['counts']['pending'] }} pending</span><span class="count-locked"><i></i>{{ $workflow['counts']['locked'] }} locked</span></div>
                </div>
                <div class="workflow-tools" aria-label="Step display filters">
                    <span class="workflow-tools-label">Show steps</span>
                    <button type="button" class="step-filter is-active" data-step-filter="all" aria-pressed="true">All</button>
                    <button type="button" class="step-filter" data-step-filter="actionable" aria-pressed="false">Active / Pending</button>
                </div>
                <div class="phase-nav" role="tablist" aria-label="Program phases" data-phase-tabs>
                    @foreach($workflow['phases'] as $phase => $phaseData)
                        <button type="button" id="phase-tab-{{ $phase }}" class="phase-tab is-{{ $phaseData['state'] }} @if((int) $phase === (int) $activePhase) is-current @endif" role="tab" aria-selected="{{ (int) $phase === (int) $activePhase ? 'true' : 'false' }}" aria-controls="phase-panel-{{ $phase }}" tabindex="{{ (int) $phase === (int) $activePhase ? '0' : '-1' }}" data-phase-target="{{ $phase }}">
                            <span class="phase-number">@if($phaseData['state'] === 'completed')<i class="mdi mdi-check"></i>@else{{ str_pad((string) $phase, 2, '0', STR_PAD_LEFT) }}@endif</span>
                            <span class="phase-tab-copy">
                                <span class="phase-tab-heading">
                                    <span class="phase-tab-title">{{ $phaseData['name'] }}</span>
                                    @if((int) $phase === (int) $activePhase)
                                        <span class="phase-live" aria-label="Current workflow phase" title="Current workflow phase"><span class="phase-live-dot" aria-hidden="true"></span></span>
                                    @endif
                                </span>
                                <span class="phase-tab-meta">{{ $phaseData['finished'] }}/{{ $phaseData['total'] }} steps complete</span>
                                <span class="phase-state">{{ $phaseData['state'] === 'future' ? 'Future phase' : $phaseData['state'] }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>

                <section class="next-action-card" aria-labelledby="next-action-title">
                    @if($workflow['next_activity'])
                        @php
                            $nextActivity = $workflow['next_activity'];
                        @endphp
                        <div class="next-action-kicker">Next action</div>
                        <div class="next-action-layout">
                            <div class="next-action-copy">
                                <h4 id="next-action-title" class="next-action-title">Step {{ $nextActivity->step_code }} — {{ $nextActivity->title }}</h4>
                                <div class="next-action-meta"><x-eclip.workflow-status-badge :status="$nextActivity->status" /><x-eclip.responsible-agency :labels="$workflow['next_meta']['responsible']" />@if($nextActivity->due_at)<span class="responsible-agency"><i class="mdi mdi-calendar-clock" aria-hidden="true"></i>Due {{ $nextActivity->due_at->format('M d, Y') }}</span>@endif</div>
                                <p class="next-action-help">@if($workflow['next_meta']['can_update'])This step is ready for action by your office.@else Waiting for {{ implode(' / ', $workflow['next_meta']['responsible']) }} to complete this step.@endif</p>
                            </div>
                            <button type="button" class="next-action-button" data-open-step="{{ $nextActivity->id }}" data-open-phase="{{ $nextActivity->phase }}">{{ $workflow['next_meta']['can_update'] ? 'Open Step' : 'View Details' }} <i class="mdi mdi-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    @else
                        <div class="next-action-empty"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i><div><strong id="next-action-title">No action required at this time</strong><span>There is no currently actionable workflow step for this case.</span></div></div>
                    @endif
                </section>

                @foreach($workflow['phases'] as $phase => $phaseData)
                    @php
                        $phaseIndex = $workflow['phases']->keys()->search($phase);
                        $previousPhase = $workflow['phases']->values()->get($phaseIndex - 1);
                        $nextPhase = $workflow['phases']->values()->get($phaseIndex + 1);
                    @endphp
                    <section id="phase-panel-{{ $phase }}" class="phase-panel" role="tabpanel" aria-labelledby="phase-tab-{{ $phase }}" data-phase-panel="{{ $phase }}" @if((int) $phase !== (int) $activePhase) hidden @endif>
                        <header class="phase-panel-header">
                            <div>
                                <h4 class="phase-panel-title">Phase {{ $phase }} · {{ $phaseData['name'] }}</h4>
                                <p class="phase-panel-description">{{ $phaseData['description'] }}</p>
                                <p class="phase-panel-meta">{{ $phaseData['finished'] }} of {{ $phaseData['total'] }} steps completed</p>
                            </div>
                            <div class="phase-controls">
                                <button type="button" class="phase-control" data-phase-direction="previous" @disabled(! $previousPhase)><i class="mdi mdi-chevron-left"></i>{{ $previousPhase ? $previousPhase['name'] : 'Previous Phase' }}</button>
                                <button type="button" class="phase-control" data-phase-direction="next" @disabled(! $nextPhase)>{{ $nextPhase ? $nextPhase['name'] : 'Next Phase' }}<i class="mdi mdi-chevron-right"></i></button>
                            </div>
                        </header>
                        <div class="phase-steps">
                            @foreach($phaseData['activities'] as $activity)
                                @php
                                    $statusSlug = str($activity->status)->slug();
                                    $meta = $workflow['activity_meta']->get($activity->id);
                                    $isNext = $workflow['next_activity']?->is($activity) ?? false;
                                @endphp
                                <details id="workflow-step-{{ $activity->id }}" class="official-step status-{{ $statusSlug }} {{ $isNext ? 'is-next-step' : '' }}" data-step-item data-step-id="{{ $activity->id }}" data-step-state="{{ $meta['is_actionable'] ? 'actionable' : $activity->status }}" @if($isNext) open @endif>
                                    <summary class="step-summary" aria-expanded="{{ $isNext ? 'true' : 'false' }}">
                                        <span class="step-marker">@if(in_array($activity->status, ['completed', 'not_applicable'], true))<i class="mdi mdi-check"></i>@elseif($activity->status === 'locked')<i class="mdi mdi-lock-outline"></i>@else{{ $activity->step_code }}@endif</span>
                                        <span class="step-content">
                                            <span class="step-name">Step {{ $activity->step_code }} — {{ $activity->title }}</span>
                                            <span class="step-meta"><x-eclip.workflow-status-badge :status="$activity->status" /><x-eclip.responsible-agency :labels="$meta['responsible']" />@if($activity->due_at)<span><i class="mdi mdi-clock-outline"></i> Due {{ $activity->due_at->format('M d, Y') }}</span>@endif @if($activity->status === 'locked' && $meta['blocking_dependencies']->isNotEmpty())<span class="step-lock-reason">Requires {{ $meta['blocking_dependencies']->pluck('code')->map(fn ($code) => "Step {$code}")->join(' and ') }}</span>@endif</span>
                                        </span>
                                        <i class="mdi mdi-chevron-down step-chevron" aria-hidden="true"></i>
                                    </summary>
                                    <div class="step-expanded">
                                        <div class="step-detail-grid">
                                            <div class="step-detail"><span class="step-detail-label">Responsible</span><x-eclip.responsible-agency :labels="$meta['responsible']" />@if($meta['responsible_people'] !== [])<span class="step-detail-value mt-1">Assigned: {{ implode(', ', $meta['responsible_people']) }}</span>@endif</div>
                                            <div class="step-detail"><span class="step-detail-label">Processing time</span><span class="step-detail-value {{ $meta['deadline']['state'] === 'overdue' ? 'text-danger font-weight-bold' : '' }}">{{ $activity->due_at?->format('M d, Y') ?? 'No due date recorded' }} · {{ $meta['deadline']['label'] }}</span></div>
                                            @if(! empty($activity->required_documents))<div class="step-detail"><span class="step-detail-label">Required documents</span><span class="step-detail-value">{{ implode(' · ', $activity->required_documents) }}</span></div>@endif
                                            @if($activity->remarks)<div class="step-detail"><span class="step-detail-label">Remarks</span><span class="step-detail-value">{{ $activity->remarks }}</span></div>@endif
                                            @if(! empty($activity->data))
                                                @php
                                                    $stepDefinition = collect(config('eclip_workflow.steps'))->firstWhere('code', $activity->step_code) ?? [];
                                                    $stepFields = $stepDefinition['fields'] ?? [];
                                                @endphp
                                                @foreach($stepFields as $field)
                                                    @if(array_key_exists($field['key'], $activity->data) && filled($activity->data[$field['key']]))
                                                        <div class="step-detail"><span class="step-detail-label">{{ $field['label'] }}</span><span class="step-detail-value">{{ ($field['type'] ?? null) === 'checkbox' ? 'Yes' : $activity->data[$field['key']] }}</span></div>
                                                    @endif
                                                @endforeach
                                            @endif
                                            @if($activity->status === 'locked' && $meta['dependencies']->isNotEmpty())<div class="step-detail"><span class="step-detail-label">Unlock requirements</span><ul class="step-dependencies">@foreach($meta['dependencies'] as $dependency)<li><i class="mdi {{ $dependency['complete'] ? 'mdi-check-circle-outline text-success' : 'mdi-lock-outline' }}" aria-hidden="true"></i><span>Step {{ $dependency['code'] }} — {{ $dependency['title'] }}</span></li>@endforeach</ul></div>@endif
                                            <div class="step-detail"><span class="step-detail-label">Activity</span><span class="step-detail-value">Last updated {{ $activity->updated_at->format('M d, Y · h:i A') }}</span></div>
                                        </div>
                                        @if(! empty($activity->required_documents))
                                            <div class="step-required-docs mt-3">
                                                <span class="step-required-docs-title"><i class="mdi mdi-folder-lock" aria-hidden="true"></i> Secure step evidence and version history</span>
                                                @foreach($activity->required_documents as $requiredDocument)
                                                    @php
                                                        $versions = $activity->documents->where('document_type', $requiredDocument)->sortByDesc('version_number');
                                                    @endphp
                                                    <div class="step-required-doc align-items-center justify-content-between">
                                                        <span><i class="mdi {{ $versions->isNotEmpty() ? 'mdi-check-circle-outline text-success' : 'mdi-alert-circle-outline text-warning' }}"></i>{{ $requiredDocument }} · {{ $versions->isNotEmpty() ? 'Uploaded' : 'Missing' }}</span>
                                                        @if($versions->isNotEmpty())<span>@foreach($versions as $version)<a class="version-link" href="{{ route('eclip.workflow-documents.download', $version) }}">v{{ $version->version_number }}</a>@endforeach</span>@endif
                                                    </div>
                                                @endforeach
                                                @can('uploadWorkflowDocument', $activity)
                                                    <form method="POST" action="{{ route('eclip.workflow-documents.store', $activity) }}" enctype="multipart/form-data" class="supporting-upload mt-3">
                                                        @csrf
                                                        <label class="sr-only" for="workflow-document-type-{{ $activity->id }}">Evidence type</label>
                                                        <select id="workflow-document-type-{{ $activity->id }}" name="document_type" class="form-control mb-2" required><option value="">Select evidence type</option>@foreach($activity->required_documents as $requiredDocument)<option value="{{ $requiredDocument }}">{{ $requiredDocument }}</option>@endforeach</select>
                                                        <label class="sr-only" for="workflow-document-{{ $activity->id }}">Evidence file</label>
                                                        <input id="workflow-document-{{ $activity->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="form-control-file mb-2" required>
                                                        <button class="btn btn-sm btn-outline-primary"><i class="mdi mdi-upload mr-1"></i>Upload evidence</button>
                                                    </form>
                                                @endcan
                                            </div>
                                        @endif
                                        @if(! $activity->remarks && empty($activity->required_documents) && $activity->status !== 'locked')<p class="step-empty-detail mt-2">No additional requirements or remarks are recorded.</p>@endif
                                        @if($meta['can_update'])<button type="button" class="step-action-button" data-bs-toggle="modal" data-bs-target="#step-modal-{{ $activity->id }}"><i class="mdi mdi-pencil-outline"></i> Update step</button>@elseif($meta['is_actionable'])<p class="step-empty-detail mt-2">You can view this step, but only {{ implode(' / ', $meta['responsible']) }} can update it.</p>@endif
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>

        @foreach($workflowActivities as $activity)
            @can('updateWorkflowActivity', $activity)
                @php
                    $stepDefinition = collect(config('eclip_workflow.steps'))->firstWhere('code', $activity->step_code);
                @endphp
                <div class="modal fade step-modal" id="step-modal-{{ $activity->id }}" tabindex="-1" role="dialog" aria-labelledby="step-modal-title-{{ $activity->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                        <form method="POST" action="{{ route('eclip.workflow-activities.update', $activity) }}" class="modal-content step-action-form" data-step-update-form>
                            @csrf
                            @method('PATCH')
                            <div class="modal-header">
                                <div>
                                    <span class="step-modal-kicker">Phase {{ $activity->phase }} · Step {{ $activity->step_code }}</span>
                                    <h5 class="modal-title" id="step-modal-title-{{ $activity->id }}">{{ $activity->title }}</h5>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <h6 class="step-form-heading">Update activity status</h6>
                                <p class="step-form-help">Select the status that accurately reflects the work completed by your office.</p>
                                <div class="step-status-options">
                                    @if($activity->status !== 'ongoing')
                                        <label class="step-status-option">
                                            <input type="radio" name="status" value="ongoing" required>
                                            <span class="step-status-choice"><i class="mdi mdi-play-outline"></i><span><strong>Start activity</strong><small>Work is now in progress</small></span></span>
                                        </label>
                                    @endif
                                    <label class="step-status-option choice-complete">
                                        <input type="radio" name="status" value="completed" required>
                                        <span class="step-status-choice"><i class="mdi mdi-check-bold"></i><span><strong>Mark completed</strong><small>All requirements are satisfied</small></span></span>
                                    </label>
                                    @if($stepDefinition['allow_na'] ?? false)
                                        <label class="step-status-option">
                                            <input type="radio" name="status" value="not_applicable" required>
                                            <span class="step-status-choice"><i class="mdi mdi-minus-circle-outline"></i><span><strong>Not applicable</strong><small>This activity does not apply to the case</small></span></span>
                                        </label>
                                    @endif
                                    @if(in_array($activity->step_code, ['6D', '6E', '6F'], true))
                                        <label class="step-status-option choice-return">
                                            <input type="radio" name="status" value="returned_for_correction" required data-requires-remarks>
                                            <span class="step-status-choice"><i class="mdi mdi-undo-variant"></i><span><strong>Return for correction</strong><small>Send the activity back to the previous office</small></span></span>
                                        </label>
                                    @endif
                                </div>
                                <label for="activity-remarks-{{ $activity->id }}" class="step-input-label">Remarks <span data-step-remarks-help>Optional</span></label>
                                <textarea id="activity-remarks-{{ $activity->id }}" name="remarks" class="form-control step-remarks-input" rows="3" maxlength="5000" placeholder="Add relevant observations, notes, or instructions..." data-step-remarks></textarea>
                                @if(! empty($stepDefinition['fields']))
                                    <div class="mt-3">
                                        <h6 class="step-form-heading">Program monitoring details</h6>
                                        <p class="step-form-help">Only record information authorized for this case. Each saved value is included in the step history.</p>
                                        @foreach($stepDefinition['fields'] as $field)
                                            @php
                                                $fieldValue = old("data.{$field['key']}", data_get($activity->data, $field['key']));
                                            @endphp
                                            <div class="form-group mb-3">
                                                @if(($field['type'] ?? null) === 'checkbox')
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="hidden" name="data[{{ $field['key'] }}]" value="0">
                                                        <input type="checkbox" class="custom-control-input" id="activity-field-{{ $activity->id }}-{{ $field['key'] }}" name="data[{{ $field['key'] }}]" value="1" @checked($fieldValue)>
                                                        <label class="custom-control-label" for="activity-field-{{ $activity->id }}-{{ $field['key'] }}">{{ $field['label'] }}</label>
                                                    </div>
                                                @else
                                                    <label class="step-input-label" for="activity-field-{{ $activity->id }}-{{ $field['key'] }}">{{ $field['label'] }}</label>
                                                    @if(($field['type'] ?? null) === 'textarea')
                                                        <textarea class="form-control" id="activity-field-{{ $activity->id }}-{{ $field['key'] }}" name="data[{{ $field['key'] }}]" rows="3" maxlength="5000">{{ $fieldValue }}</textarea>
                                                    @else
                                                        <input class="form-control" id="activity-field-{{ $activity->id }}-{{ $field['key'] }}" name="data[{{ $field['key'] }}]" type="{{ $field['type'] ?? 'text' }}" value="{{ $fieldValue }}" maxlength="5000">
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(! empty($activity->required_documents))
                                    <div class="step-required-docs">
                                        <span class="step-required-docs-title"><i class="mdi mdi-file-check-outline" aria-hidden="true"></i> Required documents</span>
                                        @foreach($activity->required_documents as $requiredDocument)
                                            @php
                                                $hasEvidence = $activity->documents->contains('document_type', $requiredDocument);
                                            @endphp
                                            <span class="step-required-doc"><i class="mdi {{ $hasEvidence ? 'mdi-check-circle-outline text-success' : 'mdi-alert-circle-outline text-warning' }}"></i><span>{{ $requiredDocument }} — {{ $hasEvidence ? 'Uploaded' : 'Missing' }}</span></span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <span class="step-form-note"><i class="mdi mdi-history"></i> This update will be recorded in the audit history.</span>
                                <button type="button" class="btn btn-light step-save" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary step-save" data-step-save><i class="mdi mdi-content-save-outline mr-1" aria-hidden="true"></i>Save update</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan
        @endforeach
    @endif

            @can('reviewEligibility', $case)
                <section class="card review-card mb-4" aria-labelledby="decision-title">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="section-icon mr-3"><i class="mdi mdi-clipboard-check-outline"></i></div>
                            <div><h3 id="decision-title" class="section-title">Record Eligibility Decision</h3><p class="section-subtitle">Review the case information and select the appropriate outcome.</p></div>
                        </div>

                        <form method="POST" action="{{ route('lswdo.eclip.eligibility.decide', $case) }}" data-decision-form>
                            @csrf
                            <fieldset>
                                <legend class="remarks-label mb-2">Decision</legend>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="decision-option eligible">
                                            <input type="radio" name="decision" value="eligible" @checked(old('decision') === 'eligible') required>
                                            <span class="decision-content"><i class="mdi mdi-check-circle-outline"></i><span><strong>Eligible</strong><small>Proceed with E-CLIP processing</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="decision-option ineligible">
                                            <input type="radio" name="decision" value="previously_assisted" @checked(old('decision') === 'previously_assisted') required>
                                            <span class="decision-content"><i class="mdi mdi-history"></i><span><strong>Previously Assisted</strong><small>Stop ordinary assistance flow</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="decision-option returned">
                                            <input type="radio" name="decision" value="for_clarification" @checked(old('decision') === 'for_clarification') required>
                                            <span class="decision-content"><i class="mdi mdi-help-circle-outline"></i><span><strong>For Clarification</strong><small>Return for clarification</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="decision-option ineligible">
                                            <input type="radio" name="decision" value="not_eligible" @checked(old('decision') === 'not_eligible') required>
                                            <span class="decision-content"><i class="mdi mdi-close-circle-outline"></i><span><strong>Not Eligible</strong><small>Stop normal processing</small></span></span>
                                        </label>
                                    </div>
                                </div>
                                @error('decision')<div class="text-danger small mb-3" role="alert">{{ $message }}</div>@enderror
                            </fieldset>

                            <div class="form-group mt-2">
                                <label for="remarks" class="remarks-label">Remarks <span class="remarks-help" data-remarks-help>(optional for eligible decisions)</span></label>
                                <textarea id="remarks" name="remarks" rows="4" maxlength="5000" class="form-control @error('remarks') is-invalid @enderror" placeholder="Provide a clear reason or instructions for the submitting office...">{{ old('remarks') }}</textarea>
                                @error('remarks')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mt-3">
                                <small class="text-muted mb-2 mb-sm-0"><i class="mdi mdi-information-outline mr-1"></i>This decision will be recorded in the case history.</small>
                                <button class="btn btn-primary review-submit" data-submit-button><i class="mdi mdi-content-save-outline mr-1" aria-hidden="true"></i>Save Decision</button>
                            </div>
                        </form>
                    </div>
                </section>
            @endcan

            @if($case->status === \App\Enums\EclipCaseStatus::Eligible && !$case->authenticationRequest)
                <section class="card review-card mb-4" aria-labelledby="authentication-request-title">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="section-icon mr-3"><i class="mdi mdi-shield-check-outline"></i></div>
                            <div><h3 id="authentication-request-title" class="section-title">Request JAPIC Authentication</h3><p class="section-subtitle">Assign one authorized reviewer. Authentication requires an explicit JAPIC decision.</p></div>
                        </div>
                        <form method="POST" action="{{ route('lswdo.eclip.authentication.store', $case) }}" class="d-flex flex-column flex-md-row gap-2">
                            @csrf
                            <label class="sr-only" for="assigned-japic">Assigned JAPIC reviewer</label>
                            <select id="assigned-japic" name="assigned_to" class="form-select" required>
                                <option value="">Select JAPIC reviewer</option>
                                @foreach($japicUsers as $reviewer)<option value="{{ $reviewer->id }}">{{ $reviewer->name }}</option>@endforeach
                            </select>
                            <button class="btn btn-primary text-nowrap">Assign and request</button>
                        </form>
                    </div>
                </section>
            @elseif($case->authenticationRequest)
                <section class="card review-card mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div><h3 class="section-title">JAPIC Authentication</h3><p class="section-subtitle">Assigned to {{ $case->authenticationRequest->assignee->name }}</p></div>
                        <x-eclip.workflow-status-badge :status="$case->authenticationRequest->status" />
                    </div>
                </section>
            @endif

        </main>
        <aside class="case-sidebar" aria-label="Case documents, services, and history">
            <section class="card review-card mb-4" aria-labelledby="documents-title">
                <div class="card-body">
                    <div class="sidebar-card-header">
                        <div class="section-icon"><i class="mdi mdi-folder-multiple-outline" aria-hidden="true"></i></div>
                        <div><h3 id="documents-title" class="section-title">Supporting Documents</h3><p class="section-subtitle">Official case requirements and files.</p></div>
                        @if($requirements->isNotEmpty())<span class="sidebar-count">{{ $submittedDocuments }} / {{ $requirements->count() }}</span>@endif
                    </div>
                    @if($requirements->isEmpty())
                        <div class="sidebar-alert"><i class="mdi mdi-alert-outline" aria-hidden="true"></i><span><strong>Document checklist unavailable</strong><br>The official checklist has not yet been configured by Katuparan Center.</span></div>
                    @else
                        <div class="checklist-progress"><strong>{{ $submittedDocuments }}</strong> of <strong>{{ $requirements->count() }}</strong> requirements have an uploaded document.</div>
                        <details class="sidebar-disclosure" @if($errors->has('document')) open @endif>
                            <summary>View document checklist <i class="mdi mdi-chevron-down" aria-hidden="true"></i></summary>
                            <div class="sidebar-document-list">
                                @foreach($requirements as $requirement)
                                    @php
                                        $document = $documentsByRequirement->get($requirement->id);
                                        $documentStatus = $document?->status ?? 'missing';
                                    @endphp
                                    <article class="sidebar-document-item">
                                        <div class="sidebar-document-heading"><strong>{{ $requirement->name }} @if($requirement->is_required)<span class="required-mark" title="Required">*</span>@endif</strong><span class="document-status status-{{ str($documentStatus)->slug() }}">{{ str($documentStatus)->replace('_', ' ')->title() }}</span></div>
                                        <div class="version-grid mt-2">
                                            @forelse($document?->versions?->sortByDesc('version_number') ?? [] as $version)
                                                <a class="document-preview-link" href="{{ route('lswdo.eclip.documents.preview', $version) }}" title="Preview {{ $version->original_name }}"><i class="mdi mdi-eye-outline"></i><span><strong>Version {{ $version->version_number }} @if($loop->first)<span class="latest-badge">Latest</span>@endif</strong><small>{{ $version->original_name }}</small></span></a>
                                            @empty
                                                <div class="no-document"><i class="mdi mdi-file-hidden"></i>No document uploaded</div>
                                            @endforelse
                                        </div>
                                        @can('uploadDocument', $case)
                                            <form method="POST" action="{{ route('lswdo.eclip.documents.store', $case) }}" enctype="multipart/form-data" class="supporting-upload sidebar-upload" data-document-upload>
                                                @csrf
                                                <input type="hidden" name="requirement_id" value="{{ $requirement->id }}">
                                                <input id="requirement-file-{{ $requirement->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required data-document-input>
                                                <label for="requirement-file-{{ $requirement->id }}" class="supporting-upload-picker"><span class="supporting-upload-icon"><i class="mdi mdi-cloud-upload-outline"></i></span><span><span class="supporting-upload-title">Choose a file</span><span class="supporting-upload-help text-muted">PDF, JPG, or PNG · Maximum 10 MB</span></span></label>
                                                <span class="supporting-upload-name" data-document-name>No file selected</span>
                                                @error('document')<div class="text-danger small mb-2" role="alert">{{ $message }}</div>@enderror
                                                <button class="btn btn-sm btn-outline-primary btn-block" data-upload-button><i class="mdi mdi-upload mr-1"></i>{{ $document ? 'Upload New Version' : 'Upload Document' }}</button>
                                            </form>
                                        @endcan
                                    </article>
                                @endforeach
                            </div>
                            @can('uploadDocument', $case)<p class="section-subtitle mt-2 mb-0"><i class="mdi mdi-history mr-1"></i>Replacement uploads retain earlier versions and reset JAPIC review.</p>@endcan
                        </details>
                    @endif
                </div>
            </section>

            <section class="card review-card mb-4" aria-labelledby="actions-title">
                <div class="card-body">
                    <div class="sidebar-card-header">
                        <div class="section-icon"><i class="mdi mdi-heart-pulse"></i></div>
                        <div><h3 id="actions-title" class="section-title">Case Services</h3><p class="section-subtitle">Related reintegration monitoring.</p></div>
                    </div>
                    <a href="{{ route('lswdo.eclip.basic-services.index', $case) }}" class="service-link"><i class="mdi mdi-clipboard-pulse-outline"></i><span>Monitor Basic Services</span><i class="mdi mdi-chevron-right"></i></a>
                </div>
            </section>

            <section class="card review-card mb-4" aria-labelledby="fea-title">
                <div class="card-body">
                    <div class="sidebar-card-header"><div class="section-icon"><i class="mdi mdi-shield-key-outline"></i></div><div><h3 id="fea-title" class="section-title">FEA Processing Records</h3><p class="section-subtitle">PNP/AFP uploads related to firearms, explosives, and ammunition.</p></div></div>
                    @php($assignedFeaProcessor = $case->participantAssignments->first(fn ($assignment) => $assignment->participant_role === 'fea_processor' && $assignment->is_active))
                    @can('assignFeaProcessor', $case)
                        <form method="POST" action="{{ route('lswdo.eclip.fea-processor.store', $case) }}" class="mt-3">@csrf
                            <label class="step-input-label" for="fea-processor">Assigned PNP/AFP processor</label>
                            <div class="d-flex"><select id="fea-processor" name="processor_id" class="form-control mr-2" required><option value="">Select processor</option>@foreach($feaProcessors as $processor)<option value="{{ $processor->id }}" @selected($assignedFeaProcessor?->user_id === $processor->id)>{{ $processor->name }} · {{ str($processor->role)->upper() }}</option>@endforeach</select><button class="btn btn-outline-primary text-nowrap">Assign</button></div>
                            @error('processor_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </form>
                    @else
                        @if($assignedFeaProcessor)<p class="section-subtitle mt-3"><i class="mdi mdi-account-check-outline mr-1"></i>Assigned to {{ $assignedFeaProcessor->user->name }} · {{ str($assignedFeaProcessor->user->role)->upper() }}</p>@endif
                    @endcan
                    <div class="sidebar-document-list mt-3">@forelse($case->feaDocuments->sortByDesc('created_at') as $feaDocument)<a class="document-preview-link" href="{{ route('eclip-fea.documents.download', $feaDocument) }}"><i class="mdi mdi-file-eye-outline"></i><span><strong>{{ str($feaDocument->document_type)->upper() }}</strong><small>{{ $feaDocument->original_name }} · {{ $feaDocument->created_at->format('M d, Y') }}</small></span></a>@empty<div class="no-document"><i class="mdi mdi-file-hidden"></i>No FEA records uploaded.</div>@endforelse</div>
                </div>
            </section>

            <section class="card review-card mb-4" aria-labelledby="interventions-title">
                <div class="card-body">
                    <div class="sidebar-card-header"><div class="section-icon"><i class="mdi mdi-account-heart-outline" aria-hidden="true"></i></div><div><h3 id="interventions-title" class="section-title">Services and Reintegration</h3><p class="section-subtitle">Repeatable social-protection and reintegration entries.</p></div></div>
                    @can('reviewEligibility', $case)
                        <p class="section-subtitle mt-3">Record eligibility before adding interventions.</p>
                    @else
                        @if($case->hasActiveParticipant(auth()->user(), 'case_processor'))
                            <form method="POST" action="{{ route('lswdo.eclip.interventions.store', $case) }}" class="mt-3">@csrf
                                <div class="form-group"><label class="step-input-label" for="intervention-stage">Stage</label><select id="intervention-stage" name="stage" class="form-control"><option value="social_protection">Social protection</option><option value="reintegration">Reintegration</option></select></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-title">Service or intervention</label><input id="intervention-title" name="title" class="form-control" maxlength="255" required></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-provider">Provider</label><input id="intervention-provider" name="provider" class="form-control" maxlength="255"></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-amount">Amount or value</label><input id="intervention-amount" name="amount_or_value" class="form-control" type="number" min="0" step="0.01"></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-target">Target date</label><input id="intervention-target" name="target_date" class="form-control" type="date"></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-status">Status</label><select id="intervention-status" name="status" class="form-control"><option value="pending">Pending</option><option value="referred">Referred</option><option value="in_progress">In progress</option><option value="completed">Completed</option><option value="returned">Returned</option><option value="not_applicable">Not applicable</option></select></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-outcome">Outcome <span>Required when completed</span></label><textarea id="intervention-outcome" name="outcome" class="form-control" rows="2" maxlength="5000"></textarea></div>
                                <div class="form-group"><label class="step-input-label" for="intervention-remarks">Remarks / delay, return, or not-applicable reason</label><textarea id="intervention-remarks" name="remarks" class="form-control" rows="2" maxlength="5000"></textarea></div>
                                <button class="btn btn-sm btn-outline-primary btn-block">Add entry</button>
                            </form>
                        @endif
                    @endcan
                    <div class="sidebar-document-list mt-3">@forelse($case->interventions->sortByDesc('created_at') as $intervention)<details class="sidebar-document-item"><summary class="sidebar-document-heading"><strong>{{ $intervention->title }}</strong><span class="document-status status-pending">{{ str($intervention->status)->replace('_', ' ')->title() }}</span></summary><small class="text-muted d-block mt-1">{{ str($intervention->stage)->replace('_', ' ')->title() }}@if($intervention->provider) · {{ $intervention->provider }}@endif @if($intervention->target_date) · Target {{ $intervention->target_date->format('M d, Y') }}@endif</small>@if($intervention->outcome)<p class="history-remarks mt-2 mb-0"><strong>Outcome:</strong> {{ $intervention->outcome }}</p>@endif @if($case->hasActiveParticipant(auth()->user(), 'case_processor'))<form method="POST" action="{{ route('lswdo.eclip.interventions.update', $intervention) }}" class="mt-2">@csrf @method('PUT')<input type="hidden" name="stage" value="{{ $intervention->stage }}"><input type="hidden" name="title" value="{{ $intervention->title }}"><input type="hidden" name="provider" value="{{ $intervention->provider }}"><input type="hidden" name="amount_or_value" value="{{ $intervention->amount_or_value }}"><input type="hidden" name="target_date" value="{{ $intervention->target_date?->toDateString() }}"><label class="step-input-label" for="intervention-edit-status-{{ $intervention->id }}">Update status</label><select id="intervention-edit-status-{{ $intervention->id }}" name="status" class="form-control mb-2">@foreach(\App\Models\EclipIntervention::STATUSES as $status)<option value="{{ $status }}" @selected($intervention->status === $status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select><label class="step-input-label" for="intervention-edit-outcome-{{ $intervention->id }}">Outcome</label><textarea id="intervention-edit-outcome-{{ $intervention->id }}" name="outcome" class="form-control mb-2" rows="2" maxlength="5000">{{ $intervention->outcome }}</textarea><label class="step-input-label" for="intervention-edit-remarks-{{ $intervention->id }}">Reason / next required action</label><textarea id="intervention-edit-remarks-{{ $intervention->id }}" name="remarks" class="form-control mb-2" rows="2" maxlength="5000">{{ $intervention->remarks }}</textarea><button class="btn btn-sm btn-outline-primary btn-block">Save intervention update</button></form>@endif</details>@empty<div class="no-document"><i class="mdi mdi-clipboard-text-outline"></i>No services or interventions recorded.</div>@endforelse</div>
                </div>
            </section>

            <section class="card review-card mb-4" aria-labelledby="history-title">
                <div class="card-body">
                    <div class="sidebar-card-header">
                        <div class="section-icon"><i class="mdi mdi-history"></i></div>
                        <div><h3 id="history-title" class="section-title">Case History</h3><p class="section-subtitle">Recorded workflow activity.</p></div>
                    </div>
                    <ol class="history-timeline">
                        @forelse($case->statusHistories->sortByDesc('created_at')->take(5) as $history)
                            <li class="history-item">
                                <span class="history-dot" aria-hidden="true"></span>
                                <div class="history-status">{{ $history->to_status->label() }}</div>
                                <div class="history-meta"><i class="mdi mdi-account-outline"></i> {{ $history->user?->name ?? 'System' }} · <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M d, Y · h:i A') }}</time></div>
                                @if($history->remarks)<div class="history-remarks">{{ $history->remarks }}</div>@endif
                            </li>
                        @empty
                            <li class="text-muted small">No case activity has been recorded.</li>
                        @endforelse
                    </ol>
                    @if($case->statusHistories->count() > 5)
                        <details class="sidebar-history-more"><summary>View full history ({{ $case->statusHistories->count() }})</summary><ol class="history-timeline mt-3">@foreach($case->statusHistories->sortByDesc('created_at')->skip(5) as $history)<li class="history-item"><span class="history-dot" aria-hidden="true"></span><div class="history-status">{{ $history->to_status->label() }}</div><div class="history-meta"><i class="mdi mdi-account-outline"></i> {{ $history->user?->name ?? 'System' }} · <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M d, Y · h:i A') }}</time></div>@if($history->remarks)<div class="history-remarks">{{ $history->remarks }}</div>@endif</li>@endforeach</ol></details>
                    @endif
                    @php($stepHistories = $case->workflowActivities->flatMap(fn ($activity) => $activity->histories->map(fn ($history) => ['activity' => $activity, 'history' => $history]))->sortByDesc(fn ($entry) => $entry['history']->created_at))
                    @if($stepHistories->isNotEmpty())
                        <details class="sidebar-history-more"><summary>View step history ({{ $stepHistories->count() }})</summary><ol class="history-timeline mt-3">@foreach($stepHistories as $entry)<li class="history-item"><span class="history-dot" aria-hidden="true"></span><div class="history-status">Step {{ $entry['activity']->step_code }} · {{ str($entry['history']->event ?? 'status_changed')->replace('_', ' ')->title() }} · {{ str($entry['history']->to_status)->replace('_', ' ')->title() }}</div><div class="history-meta"><i class="mdi mdi-account-outline"></i> {{ $entry['history']->user?->name ?? 'System' }} · {{ $entry['history']->actor_office ?: config('shield.roles.'.$entry['history']->actor_role.'.label') }} · <time datetime="{{ $entry['history']->created_at->toIso8601String() }}">{{ $entry['history']->created_at->format('M d, Y · h:i A') }}</time></div>@if($entry['history']->remarks)<div class="history-remarks">{{ $entry['history']->remarks }}</div>@endif</li>@endforeach</ol></details>
                    @endif
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
var phaseTabs = Array.from(document.querySelectorAll('[data-phase-target]'));
var phasePanels = Array.from(document.querySelectorAll('[data-phase-panel]'));

document.querySelectorAll('.step-modal').forEach(function (modal) {
    document.body.appendChild(modal);

    modal.addEventListener('shown.bs.modal', function () {
        var firstStatus = modal.querySelector('input[name="status"]');
        if (firstStatus) firstStatus.focus();
    });
});

function showPhase(phase, updateHash) {
    var selectedTab = phaseTabs.find(function (tab) { return tab.dataset.phaseTarget === String(phase); });
    if (!selectedTab) return;

    phaseTabs.forEach(function (tab) {
        var selected = tab === selectedTab;
        tab.setAttribute('aria-selected', selected ? 'true' : 'false');
        tab.setAttribute('tabindex', selected ? '0' : '-1');
    });
    phasePanels.forEach(function (panel) {
        panel.hidden = panel.dataset.phasePanel !== String(phase);
    });
    if (updateHash && window.history && window.history.replaceState) {
        window.history.replaceState(null, '', '#phase-' + phase);
    }
}

phaseTabs.forEach(function (tab) {
    tab.addEventListener('click', function () { showPhase(tab.dataset.phaseTarget, true); });
    tab.addEventListener('keydown', function (event) {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        var index = phaseTabs.indexOf(tab);
        var nextIndex = event.key === 'ArrowRight' ? Math.min(index + 1, phaseTabs.length - 1) : Math.max(index - 1, 0);
        phaseTabs[nextIndex].focus();
        showPhase(phaseTabs[nextIndex].dataset.phaseTarget, true);
    });
});

document.querySelectorAll('[data-phase-direction]').forEach(function (button) {
    button.addEventListener('click', function () {
        var panel = button.closest('[data-phase-panel]');
        var index = phasePanels.indexOf(panel);
        var targetIndex = button.dataset.phaseDirection === 'next' ? index + 1 : index - 1;
        if (phasePanels[targetIndex]) {
            showPhase(phasePanels[targetIndex].dataset.phasePanel, true);
            document.getElementById('official-workflow-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

var requestedPhase = window.location.hash.match(/^#phase-(\d+)$/);
if (requestedPhase) showPhase(requestedPhase[1], false);

document.querySelectorAll('[data-step-item]').forEach(function (step) {
    step.addEventListener('toggle', function () {
        var summary = step.querySelector('summary');
        if (summary) summary.setAttribute('aria-expanded', step.open ? 'true' : 'false');
        if (!step.open) return;
        document.querySelectorAll('[data-step-item][open]').forEach(function (otherStep) {
            if (otherStep !== step) otherStep.open = false;
        });
    });
});

document.querySelectorAll('[data-open-step]').forEach(function (button) {
    button.addEventListener('click', function () {
        showPhase(button.dataset.openPhase, true);
        var step = document.querySelector('[data-step-id="' + button.dataset.openStep + '"]');
        if (!step) return;
        step.open = true;
        step.scrollIntoView({ behavior: 'smooth', block: 'center' });
        window.setTimeout(function () { step.querySelector('summary')?.focus(); }, 350);
    });
});

document.querySelectorAll('[data-step-filter]').forEach(function (button) {
    button.addEventListener('click', function () {
        var filter = button.dataset.stepFilter;

        document.querySelectorAll('[data-step-filter]').forEach(function (candidate) {
            var selected = candidate === button;
            candidate.classList.toggle('is-active', selected);
            candidate.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });

        document.querySelectorAll('[data-step-item]').forEach(function (step) {
            step.hidden = filter === 'actionable' && step.dataset.stepState !== 'actionable';
        });
    });
});

document.querySelectorAll('[data-step-update-form]').forEach(function (form) {
    var remarks = form.querySelector('[data-step-remarks]');
    var remarksHelp = form.querySelector('[data-step-remarks-help]');
    var saveButton = form.querySelector('[data-step-save]');

    function syncStepRemarks() {
        var selected = form.querySelector('input[name="status"]:checked');
        var required = selected && selected.hasAttribute('data-requires-remarks');
        remarks.required = Boolean(required);
        remarksHelp.textContent = required ? 'Required for returned applications' : 'Optional';
    }

    form.querySelectorAll('input[name="status"]').forEach(function (input) {
        input.addEventListener('change', syncStepRemarks);
    });
    form.addEventListener('submit', function () {
        saveButton.disabled = true;
        saveButton.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Saving update...';
    });
    syncStepRemarks();
});

document.querySelectorAll('[data-document-upload]').forEach(function (form) {
    var input = form.querySelector('[data-document-input]');
    var name = form.querySelector('[data-document-name]');
    var button = form.querySelector('[data-upload-button]');

    input.addEventListener('change', function () {
        name.textContent = input.files.length ? input.files[0].name : 'No file selected';
    });
    form.addEventListener('submit', function () {
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Uploading...';
    });
});

var decisionForm = document.querySelector('[data-decision-form]');
if (decisionForm) {
    var remarks = decisionForm.querySelector('#remarks');
    var remarksHelp = decisionForm.querySelector('[data-remarks-help]');
    var submitButton = decisionForm.querySelector('[data-submit-button]');

    function updateRemarksRequirement() {
        var selected = decisionForm.querySelector('input[name="decision"]:checked');
        var isRequired = selected && ['ineligible', 'returned'].indexOf(selected.value) !== -1;
        remarks.required = isRequired;
        remarksHelp.textContent = isRequired ? '(required for this decision)' : '(optional for eligible decisions)';
    }

    decisionForm.querySelectorAll('input[name="decision"]').forEach(function (input) {
        input.addEventListener('change', updateRemarksRequirement);
    });
    decisionForm.addEventListener('submit', function () {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i>Saving decision...';
    });
    updateRemarksRequirement();
}
</script>
@endpush
