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
    @media (min-width: 992px) {
        .case-hero { padding: .75rem 1rem; position: sticky; top: 60px; z-index: 18; }
        .case-hero > div > div:first-child { align-items: center; display: flex; gap: 1rem; min-width: 0; }
        .case-eyebrow { display: none; }
        .case-number { flex: 0 0 auto; font-size: 1.05rem; margin: 0 !important; }
        .case-details { flex: 1; flex-wrap: nowrap; gap: .35rem .8rem; }
        .case-detail { background: transparent; border: 0; gap: .4rem; min-height: 36px; padding: .15rem .35rem; }
        .case-detail > i { background: rgba(255,255,255,.12); flex-basis: 28px; height: 28px; width: 28px; }
        .case-detail small { font-size: .58rem; }
        .case-detail strong { font-size: .72rem; white-space: nowrap; }
        .case-status { margin-left: .75rem; white-space: nowrap; }
    }
    @media (prefers-reduced-motion: reduce) { .phase-live-dot { animation: none; } }
    @media (max-width: 991px) { .phase-nav { display: flex; margin-left: -1px; margin-right: -1px; overflow-x: auto; padding: 1px 1px .4rem; scroll-snap-type: x mandatory; } .phase-tab { flex: 0 0 155px; scroll-snap-align: start; } }
    @media (max-width: 991px) { .case-workspace { display: block; } .case-sidebar { max-height: none; overflow: visible; padding-right: 0; position: static; } .phase-nav { top: 0; } }
    @media (max-width: 767px) { .case-hero { padding: 1.2rem; } .case-number { font-size: 1.35rem; } .case-status { margin-top: .75rem; } .review-card .card-body { padding: 1.1rem; } .workflow-overview { align-items: stretch; flex-direction: column; gap: .55rem; } .workflow-tools { justify-content: flex-start; } .phase-panel-header { align-items: flex-start; flex-direction: column; gap: .75rem; } .step-expanded { padding-left: .8rem; } .step-status-options { grid-template-columns: 1fr; } .step-modal .modal-dialog { margin: .5rem; } .step-modal .modal-footer { align-items: stretch; flex-direction: column-reverse; gap: .6rem; } .step-save { width: 100%; } }
</style>
@endpush

@section('content')
@php
    $documentsByRequirement = $case->documents->keyBy('requirement_id');
    $submittedDocuments = $requirements->filter(fn ($requirement) => $documentsByRequirement->has($requirement->id))->count();
    $workflowActivities = $case->workflowActivities;
    $finishedActivities = $workflowActivities->whereIn('status', ['completed', 'not_applicable'])->count();
    $workflowPercent = $workflowActivities->isEmpty() ? 0 : (int) round(($finishedActivities / $workflowActivities->count()) * 100);
    $currentPhase = $workflowActivities->first(fn ($activity) => in_array($activity->status, ['pending', 'ongoing', 'late', 'returned_for_correction'], true))?->phase;
    $phaseDefinitions = collect(config('eclip_workflow.phases', []));
    $workflowPhases = $workflowActivities->groupBy('phase')->sortKeys();
    $activePhase = $currentPhase ?? $workflowPhases->keys()->last();
@endphp

<div class="review-page">
    <a href="{{ route('lswdo.eclip.index') }}" class="review-back"><i class="mdi mdi-arrow-left"></i> Back to eligibility cases</a>

    <section class="case-hero mb-4" aria-labelledby="case-number">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start position-relative" style="z-index: 1;">
            <div>
                <div class="case-eyebrow mb-1">E-CLIP Case</div>
                <h2 id="case-number" class="case-number mb-3">{{ $case->case_number }}</h2>
                <div class="case-details">
                    <div class="case-detail"><i class="mdi mdi-account-key-outline"></i><div><small>Beneficiary ID</small><strong>{{ $case->formerRebel->classified_id }}</strong></div></div>
                    <div class="case-detail"><i class="mdi mdi-map-marker-outline"></i><div><small>Municipality</small><strong>{{ $case->formerRebel->municipality?->name ?? 'Not assigned' }}</strong></div></div>
                    <div class="case-detail"><i class="mdi mdi-calendar-check-outline"></i><div><small>Submitted</small><strong>{{ $case->submitted_at?->format('M d, Y') ?? 'Not recorded' }}</strong></div></div>
                </div>
            </div>
            <span class="case-status"><i class="mdi mdi-progress-check mr-1"></i>{{ $case->status->label() }}</span>
        </div>
    </section>

    <div class="case-workspace">
        <main class="case-main">
    @if($workflowActivities->isNotEmpty())
        <section class="card review-card mb-4" aria-labelledby="official-workflow-title">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="section-icon mr-3"><i class="mdi mdi-timeline-check-outline"></i></div>
                    <div><h3 id="official-workflow-title" class="section-title">Official E-CLIP and Amnesty Workflow</h3><p class="section-subtitle">Phases and activities prescribed by the approved program flow.</p></div>
                </div>
                <div class="workflow-overview" aria-label="Workflow progress: {{ $workflowPercent }} percent">
                    <div class="workflow-progress"><div class="workflow-progress-bar" style="width: {{ $workflowPercent }}%"></div></div>
                    <span class="workflow-count">{{ $finishedActivities }}/{{ $workflowActivities->count() }} complete · {{ $workflowPercent }}%</span>
                </div>
                <div class="workflow-tools" aria-label="Step display filters">
                    <span class="workflow-tools-label">Show steps</span>
                    <button type="button" class="step-filter is-active" data-step-filter="all" aria-pressed="true">All</button>
                    <button type="button" class="step-filter" data-step-filter="actionable" aria-pressed="false">Active / Pending</button>
                </div>
                <div class="phase-nav" role="tablist" aria-label="Program phases" data-phase-tabs>
                    @foreach($workflowPhases as $phase => $activities)
                        @php
                            $phaseFinished = $activities->whereIn('status', ['completed', 'not_applicable'])->count();
                            $phaseComplete = $phaseFinished === $activities->count();
                            $phaseHasAlert = $activities->contains(fn ($activity) => in_array($activity->status, ['late', 'returned_for_correction'], true));
                        @endphp
                        <button type="button" id="phase-tab-{{ $phase }}" class="phase-tab @if($phaseComplete) is-complete @endif @if($phaseHasAlert) is-alert @endif @if((int) $phase === (int) $activePhase) is-current @endif" role="tab" aria-selected="{{ (int) $phase === (int) $activePhase ? 'true' : 'false' }}" aria-controls="phase-panel-{{ $phase }}" tabindex="{{ (int) $phase === (int) $activePhase ? '0' : '-1' }}" data-phase-target="{{ $phase }}">
                            <span class="phase-number">@if($phaseComplete)<i class="mdi mdi-check"></i>@else{{ $phase }}@endif</span>
                            <span class="phase-tab-copy">
                                <span class="phase-tab-heading">
                                    <span class="phase-tab-title">{{ data_get($phaseDefinitions, "{$phase}.name", "Phase {$phase}") }}</span>
                                    @if($currentPhase !== null && (int) $phase === (int) $currentPhase)
                                        <span class="phase-live" aria-label="Current workflow phase" title="Current workflow phase"><span class="phase-live-dot" aria-hidden="true"></span></span>
                                    @endif
                                </span>
                                <span class="phase-tab-meta">{{ $phaseFinished }}/{{ $activities->count() }} steps complete</span>
                            </span>
                        </button>
                    @endforeach
                </div>
                @foreach($workflowPhases as $phase => $activities)
                    @php
                        $phaseFinished = $activities->whereIn('status', ['completed', 'not_applicable'])->count();
                        $phaseIndex = $workflowPhases->keys()->search($phase);
                    @endphp
                    <section id="phase-panel-{{ $phase }}" class="phase-panel" role="tabpanel" aria-labelledby="phase-tab-{{ $phase }}" data-phase-panel="{{ $phase }}" @if((int) $phase !== (int) $activePhase) hidden @endif>
                        <header class="phase-panel-header">
                            <div>
                                <h4 class="phase-panel-title">Phase {{ $phase }} · {{ data_get($phaseDefinitions, "{$phase}.name", "Phase {$phase}") }}</h4>
                                <p class="phase-panel-description">{{ data_get($phaseDefinitions, "{$phase}.description") }}</p>
                                <p class="phase-panel-meta">{{ $phaseFinished }} of {{ $activities->count() }} steps completed</p>
                            </div>
                            <div class="phase-controls">
                                <button type="button" class="phase-control" data-phase-direction="previous" @disabled($phaseIndex === 0)><i class="mdi mdi-chevron-left"></i> Previous</button>
                                <button type="button" class="phase-control" data-phase-direction="next" @disabled($phaseIndex === $workflowPhases->count() - 1)>Next <i class="mdi mdi-chevron-right"></i></button>
                            </div>
                        </header>
                        <div class="phase-steps">
                            @foreach($activities as $activity)
                                @php
                                    $stepDefinition = collect(config('eclip_workflow.steps'))->firstWhere('code', $activity->step_code);
                                    $statusSlug = str($activity->status)->slug();
                                @endphp
                                @php
                                    $isActionable = in_array($activity->status, ['pending', 'ongoing', 'late', 'returned_for_correction'], true);
                                @endphp
                                <details class="official-step status-{{ $statusSlug }}" data-step-item data-step-state="{{ $isActionable ? 'actionable' : $activity->status }}" @if($isActionable) open @endif>
                                    <summary class="step-summary">
                                        <span class="step-marker">@if($activity->status === 'completed')<i class="mdi mdi-check"></i>@elseif($activity->status === 'locked')<i class="mdi mdi-lock-outline"></i>@else{{ $activity->step_code }}@endif</span>
                                        <span class="step-content">
                                            <span class="step-name">Step {{ $activity->step_code }} — {{ $activity->title }}</span>
                                            <span class="step-meta"><span class="step-badge step-badge-{{ $statusSlug }}">{{ str($activity->status)->replace('_', ' ') }}</span>@if($activity->due_at)<span><i class="mdi mdi-clock-outline"></i> Due {{ $activity->due_at->format('M d, Y') }}</span>@endif</span>
                                        </span>
                                        <i class="mdi mdi-chevron-down step-chevron" aria-hidden="true"></i>
                                    </summary>
                                    <div class="step-expanded">
                                        @if($activity->remarks)<div class="step-remarks"><i class="mdi mdi-message-text-outline"></i> {{ $activity->remarks }}</div>@endif
                                        @if(! empty($activity->required_documents))
                                            <div class="step-inline-documents"><strong>Required documents</strong><span>{{ implode(' · ', $activity->required_documents) }}</span></div>
                                        @endif
                                        @if(! $activity->remarks && empty($activity->required_documents))
                                            <p class="step-empty-detail">No additional requirements or remarks are recorded for this step.</p>
                                        @endif
                                        @can('updateWorkflowActivity', $activity)
                                            <button type="button" class="step-action-button" data-bs-toggle="modal" data-bs-target="#step-modal-{{ $activity->id }}"><i class="mdi mdi-pencil-outline"></i> Update step</button>
                                        @endcan
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
                                @if(! empty($activity->required_documents))
                                    <div class="step-required-docs">
                                        <span class="step-required-docs-title"><i class="mdi mdi-file-document-check-outline"></i> Required documents</span>
                                        @foreach($activity->required_documents as $requiredDocument)
                                            <span class="step-required-doc"><i class="mdi mdi-file-outline"></i><span>{{ $requiredDocument }}</span></span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <span class="step-form-note"><i class="mdi mdi-history"></i> This update will be recorded in the audit history.</span>
                                <button type="button" class="btn btn-light step-save" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary step-save" data-step-save><i class="mdi mdi-content-save-check-outline mr-1"></i>Save update</button>
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
                                    <div class="col-md-4">
                                        <label class="decision-option eligible">
                                            <input type="radio" name="decision" value="eligible" @checked(old('decision') === 'eligible') required>
                                            <span class="decision-content"><i class="mdi mdi-check-circle-outline"></i><span><strong>Eligible</strong><small>Proceed with E-CLIP processing</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="decision-option ineligible">
                                            <input type="radio" name="decision" value="ineligible" @checked(old('decision') === 'ineligible') required>
                                            <span class="decision-content"><i class="mdi mdi-close-circle-outline"></i><span><strong>Ineligible</strong><small>Does not meet requirements</small></span></span>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="decision-option returned">
                                            <input type="radio" name="decision" value="returned" @checked(old('decision') === 'returned') required>
                                            <span class="decision-content"><i class="mdi mdi-undo-variant"></i><span><strong>Return</strong><small>Send back for correction</small></span></span>
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
                                <button class="btn btn-primary review-submit" data-submit-button><i class="mdi mdi-content-save-check-outline mr-1"></i>Save Decision</button>
                            </div>
                        </form>
                    </div>
                </section>
            @endcan

        </main>
        <aside class="case-sidebar" aria-label="Case documents, services, and history">
            @can('uploadDocument', $case)
                <section class="card review-card mb-4" aria-labelledby="documents-title">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center mr-3">
                                <div class="section-icon mr-3"><i class="mdi mdi-folder-multiple-outline" aria-hidden="true"></i></div>
                                <div><h3 id="documents-title" class="section-title">Supporting Documents</h3><p class="section-subtitle">Review existing files or upload a new version.</p></div>
                            </div>
                            @if($requirements->isNotEmpty())
                                <span class="checklist-progress mt-2 mt-sm-0"><strong>{{ $submittedDocuments }}</strong> of <strong>{{ $requirements->count() }}</strong> requirements uploaded</span>
                            @endif
                        </div>

                        @if($requirements->isEmpty())
                            <div class="alert alert-warning document-checklist-alert mb-0"><i class="mdi mdi-alert-outline" aria-hidden="true"></i><span>The Katuparan Center has not configured the official document checklist.</span></div>
                        @else
                            @foreach($requirements as $requirement)
                                @php
                                    $document = $documentsByRequirement->get($requirement->id);
                                    $documentStatus = $document?->status ?? 'missing';
                                @endphp
                                <article class="document-item">
                                    <div class="document-heading">
                                        <div class="requirement-name">{{ $loop->iteration }}. {{ $requirement->name }} @if($requirement->is_required)<span class="required-mark" title="Required">*</span>@endif</div>
                                        <span class="document-status status-{{ str($documentStatus)->slug() }}">{{ str($documentStatus)->replace('_', ' ')->title() }}</span>
                                    </div>
                                    <div class="document-body">
                                        <div class="row align-items-start">
                                            <div class="col-lg-6 mb-3 mb-lg-0">
                                                <div class="version-grid">
                                                    @forelse($document?->versions?->sortByDesc('version_number') ?? [] as $version)
                                                        <a class="document-preview-link" href="{{ route('lswdo.eclip.documents.preview', $version) }}" title="Preview {{ $version->original_name }}">
                                                            <i class="mdi mdi-eye-outline"></i>
                                                            <span><strong>Version {{ $version->version_number }} @if($loop->first)<span class="latest-badge">Latest</span>@endif</strong><small>{{ $version->original_name }}</small></span>
                                                        </a>
                                                    @empty
                                                        <div class="no-document"><i class="mdi mdi-file-hidden"></i>No document uploaded yet</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <form method="POST" action="{{ route('lswdo.eclip.documents.store', $case) }}" enctype="multipart/form-data" class="supporting-upload" data-document-upload>
                                                    @csrf
                                                    <input type="hidden" name="requirement_id" value="{{ $requirement->id }}">
                                                    <input id="requirement-file-{{ $requirement->id }}" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required data-document-input>
                                                    <label for="requirement-file-{{ $requirement->id }}" class="supporting-upload-picker">
                                                        <span class="supporting-upload-icon"><i class="mdi mdi-cloud-upload-outline"></i></span>
                                                        <span><span class="supporting-upload-title">Choose a file</span><span class="supporting-upload-help text-muted">PDF, JPG, or PNG · Maximum 10 MB</span></span>
                                                    </label>
                                                    <span class="supporting-upload-name" data-document-name>No file selected</span>
                                                    @error('document')<div class="text-danger small mb-2" role="alert">{{ $message }}</div>@enderror
                                                    <button class="btn btn-sm btn-outline-primary btn-block" data-upload-button><i class="mdi mdi-upload mr-1"></i>{{ $document ? 'Upload New Version' : 'Upload Document' }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                            <p class="section-subtitle mt-3 mb-0"><i class="mdi mdi-history mr-1"></i>Uploading a replacement retains earlier versions and resets the JAPIC review.</p>
                        @endif
                    </div>
                </section>
            @endcan

            <section class="card review-card mb-4" aria-labelledby="actions-title">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon mr-3"><i class="mdi mdi-heart-pulse"></i></div>
                        <div><h3 id="actions-title" class="section-title">Case Services</h3><p class="section-subtitle">Related reintegration monitoring.</p></div>
                    </div>
                    <a href="{{ route('lswdo.eclip.basic-services.index', $case) }}" class="service-link"><i class="mdi mdi-clipboard-pulse-outline"></i><span>Monitor Basic Services</span><i class="mdi mdi-chevron-right"></i></a>
                </div>
            </section>

            <section class="card review-card mb-4" aria-labelledby="history-title">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="section-icon mr-3"><i class="mdi mdi-history"></i></div>
                        <div><h3 id="history-title" class="section-title">Case History</h3><p class="section-subtitle">Recorded workflow activity.</p></div>
                    </div>
                    <ol class="history-timeline">
                        @forelse($case->statusHistories->sortByDesc('created_at') as $history)
                            <li class="history-item">
                                <span class="history-dot" aria-hidden="true"></span>
                                <div class="history-status">{{ $history->to_status->label() }}</div>
                                <div class="history-meta"><i class="mdi mdi-account-outline"></i> {{ $history->user->name }} · <time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('M d, Y · h:i A') }}</time></div>
                                @if($history->remarks)<div class="history-remarks">{{ $history->remarks }}</div>@endif
                            </li>
                        @empty
                            <li class="text-muted small">No case activity has been recorded.</li>
                        @endforelse
                    </ol>
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
