@extends('layouts.skydash-v')
@section('title', 'Integration Monitoring')
@section('heading', 'Integration Monitoring')

@push('styles')
<style>
    .integration-page { --integration-purple: #6554c0; --integration-blue: #316fd6; --integration-border: #e4e9f1; --integration-muted: #718096; margin: 0 auto; max-width: 1540px; }
    .integration-header { align-items: center; background: #fff; border: 1px solid var(--integration-border); border-radius: 9px; display: flex; gap: 1rem; justify-content: space-between; margin-bottom: 1rem; padding: 1rem 1.1rem; }
    .integration-header h2 { color: #253858; font-size: 1.45rem; font-weight: 700; letter-spacing: -.015em; margin: 0 0 .3rem; }
    .integration-header p { color: #718096; font-size: .84rem; margin: 0; }
    .start-enrollment-button { align-items: center; background: var(--integration-purple); border: 1px solid var(--integration-purple); border-radius: 9px; color: #fff; display: inline-flex; font-size: .78rem; font-weight: 700; gap: .4rem; min-height: 42px; padding: .55rem .9rem; white-space: nowrap; }
    .start-enrollment-button:hover, .start-enrollment-button:focus { background: #5545ad; border-color: #5545ad; box-shadow: 0 0 0 3px rgba(101, 84, 192, .13); color: #fff; outline: 0; }
    .integration-summary { display: grid; gap: .75rem; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 1rem; }
    .summary-card { align-items: center; background: #fff; border: 1px solid var(--integration-border); border-radius: 11px; border-top-width: 3px; box-shadow: 0 2px 9px rgba(23, 43, 77, .035); display: flex; gap: .75rem; min-height: 78px; padding: .8rem .9rem; }
    .summary-icon { align-items: center; border-radius: 10px; color: #fff; display: flex; flex: 0 0 44px; font-size: 1.15rem; height: 44px; justify-content: center; width: 44px; }
    .summary-card strong { color: #344563; display: block; font-size: 1.15rem; line-height: 1; }
    .summary-card span { color: #7b8a9e; display: block; font-size: .7rem; font-weight: 650; margin-top: .28rem; }
    .summary-assigned { border-top-color: #173f7a; }
    .summary-active { border-top-color: #4b11bd; }
    .summary-completed { border-top-color: #0eaa7a; }
    .summary-attention { border-top-color: #ef4056; }
    body.mblrc-interface .integration-page .summary-assigned .summary-icon { background: #173f7a; color: #fff; box-shadow: 0 6px 14px rgba(23, 63, 122, .2); }
    body.mblrc-interface .integration-page .summary-active .summary-icon { background: #4b11bd; color: #fff; box-shadow: 0 6px 14px rgba(75, 17, 189, .2); }
    body.mblrc-interface .integration-page .summary-completed .summary-icon { background: #0eaa7a; color: #fff; box-shadow: 0 6px 14px rgba(14, 170, 122, .2); }
    body.mblrc-interface .integration-page .summary-attention .summary-icon { background: #ef4056; color: #fff; box-shadow: 0 6px 14px rgba(239, 64, 86, .2); }
    .enrollment-card { border: 1px solid var(--integration-border); border-radius: 12px; box-shadow: 0 3px 14px rgba(23, 43, 77, .045); overflow: hidden; }
    .enrollment-toolbar { align-items: end; background: #fff; border-bottom: 1px solid var(--integration-border); display: grid; gap: .7rem; grid-template-columns: minmax(260px, 1fr) minmax(160px, .4fr) minmax(170px, .45fr) auto; padding: 1rem; }
    .enrollment-field { min-width: 0; }
    .enrollment-label { color: #52616f; display: block; font-size: .72rem; font-weight: 700; margin: 0 0 .35rem; }
    .enrollment-search { position: relative; }
    .enrollment-search i { color: #8b9bb0; font-size: .95rem; left: .8rem; position: absolute; top: 50%; transform: translateY(-50%); }
    .enrollment-search .form-control { padding-left: 2.25rem; }
    .enrollment-toolbar .form-control { background-color: #fbfcfe; border: 1px solid #d9e1eb; border-radius: 8px; color: #42526b; font-size: .78rem; height: 40px; }
    .enrollment-toolbar .form-control:focus { background-color: #fff; border-color: #8c80d5; box-shadow: 0 0 0 3px rgba(101, 84, 192, .1); }
    .enrollment-filter-actions { display: flex; gap: .4rem; }
    .filter-apply, .filter-clear { align-items: center; border-radius: 8px; display: inline-flex; font-size: .75rem; font-weight: 650; gap: .35rem; height: 40px; justify-content: center; padding: 0 .8rem; white-space: nowrap; }
    .filter-apply { background: var(--integration-purple); border: 1px solid var(--integration-purple); color: #fff; }
    .filter-clear { background: #fff; border: 1px solid #d5dde8; color: #64748b; }
    .filter-apply:hover, .filter-apply:focus { background: #5545ad; color: #fff; outline: 0; }
    .filter-clear:hover, .filter-clear:focus { background: #f6f8fb; color: #34445a; outline: 0; }
    .enrollment-section-heading { align-items: center; background: #fff; border-bottom: 1px solid var(--integration-border); display: flex; justify-content: space-between; padding: .9rem 1rem; }
    .enrollment-section-heading h3 { color: #344563; font-size: .9rem; font-weight: 700; margin: 0; }
    .enrollment-result-count { color: #7b8a9e; font-size: .73rem; }
    .enrollment-table { margin: 0; table-layout: fixed; }
    .enrollment-table thead th { background: #f7f9fc; border: 0; color: #718096; font-size: .66rem; font-weight: 750; letter-spacing: .045em; padding: .8rem .9rem; text-transform: uppercase; white-space: nowrap; }
    .enrollment-table tbody td { border-color: #edf1f6; color: #52616f; font-size: .78rem; padding: .85rem .9rem; vertical-align: middle; }
    .enrollment-table tbody tr.enrollment-row:hover { background: #fbfcff; }
    .enrollment-table th:nth-child(1), .enrollment-table td:nth-child(1) { width: 18%; }
    .enrollment-table th:nth-child(2), .enrollment-table td:nth-child(2) { width: 22%; }
    .enrollment-table th:nth-child(3), .enrollment-table td:nth-child(3) { width: 14%; }
    .enrollment-table th:nth-child(4), .enrollment-table td:nth-child(4) { width: 17%; }
    .enrollment-table th:nth-child(5), .enrollment-table td:nth-child(5) { width: 17%; }
    .enrollment-table th:nth-child(6), .enrollment-table td:nth-child(6) { text-align: right; width: 12%; }
    .beneficiary-id { color: #294970; display: block; font-weight: 750; }
    .enrollment-secondary { color: #8492a6; display: block; font-size: .69rem; line-height: 1.35; margin-top: .16rem; }
    .period-primary { color: #405169; display: block; font-size: .75rem; font-weight: 650; white-space: nowrap; }
    .enrollment-status-badge { align-items: center; border-radius: 14px; display: inline-flex; font-size: .64rem; font-weight: 750; line-height: 1.2; padding: .34rem .62rem; }
    .enrollment-status-active { background: #e8f2ff; color: #2764a8; }
    .enrollment-status-completed { background: #e7f7ef; color: #187a56; }
    .enrollment-status-attention { background: #fff3dc; color: #986000; }
    .monitoring-progress strong { color: #45566d; display: block; font-size: .73rem; }
    .monitoring-track { align-items: center; display: flex; gap: 3px; margin-top: .35rem; max-width: 95px; }
    .monitoring-step { background: #e2e8f0; border-radius: 3px; display: block; height: 5px; width: 30px; }
    .monitoring-step.is-current { background: #6554c0; }
    .monitoring-progress small { color: #986000; display: block; font-size: .64rem; margin-top: .28rem; }
    .referral-status { color: #45566d; display: block; font-size: .72rem; font-weight: 650; }
    .referral-link { color: #5746af; text-decoration: underline; text-decoration-color: #c5bce9; text-underline-offset: 2px; }
    .referral-link:hover, .referral-link:focus { color: #44318f; }
    .enrollment-action { align-items: center; background: #fff; border: 1px solid #d5dde8; border-radius: 7px; color: #5746af; display: inline-flex; font-size: .7rem; font-weight: 700; gap: .3rem; justify-content: center; min-height: 36px; padding: .38rem .65rem; white-space: nowrap; }
    .enrollment-action.attention { background: #fff8e8; border-color: #edcf91; color: #8a5b00; }
    .enrollment-action:hover, .enrollment-action:focus { background: #f5f3ff; border-color: #a69bdb; color: #4f3da5; outline: 0; }
    .monitoring-panel-cell { background: #fafbfe; padding: 0 !important; }
    .monitoring-panel { border-top: 1px solid #edf1f6; padding: 1rem; }
    .monitoring-panel-header { align-items: flex-start; display: flex; gap: 1rem; justify-content: space-between; margin-bottom: .85rem; }
    .monitoring-panel h4 { color: #344563; font-size: .85rem; font-weight: 700; margin: 0 0 .2rem; }
    .monitoring-panel p { color: #718096; font-size: .72rem; margin: 0; }
    .monitoring-facts { display: grid; gap: .7rem; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: .9rem; }
    .monitoring-fact { background: #fff; border: 1px solid #e3e8f0; border-radius: 8px; padding: .65rem .7rem; }
    .monitoring-fact span { color: #8795a8; display: block; font-size: .62rem; font-weight: 750; letter-spacing: .035em; text-transform: uppercase; }
    .monitoring-fact strong { color: #405169; display: block; font-size: .75rem; margin-top: .18rem; }
    .completion-form { background: #fff; border: 1px solid #e1e7ef; border-radius: 9px; padding: .85rem; }
    .completion-form .form-label { color: #52616f; font-size: .7rem; font-weight: 700; }
    .completion-form .form-control, .completion-form .form-select { font-size: .76rem; min-height: 40px; }
    .completion-note { align-items: flex-start; background: #fff7e8; border: 1px solid #f0d49c; border-radius: 8px; color: #80580a; display: flex; font-size: .7rem; gap: .45rem; margin-bottom: .8rem; padding: .6rem .7rem; }
    .monitoring-notice { align-items: flex-start; background: #f6f8fc; border: 1px solid #e1e7f0; border-radius: 8px; color: #617087; display: flex; font-size: .72rem; gap: .5rem; line-height: 1.5; padding: .7rem .8rem; }
    .monitoring-notice i { color: #6554c0; flex: 0 0 auto; font-size: .9rem; }
    .enrollment-empty { color: #8492a6; padding: 2.8rem 1rem; text-align: center; }
    .enrollment-empty-icon { align-items: center; background: #f1effb; border-radius: 50%; color: #7667c2; display: flex; font-size: 1.35rem; height: 54px; justify-content: center; margin: 0 auto .7rem; width: 54px; }
    .enrollment-empty strong { color: #405169; display: block; font-size: .9rem; margin-bottom: .25rem; }
    .enrollment-empty p { font-size: .76rem; margin: 0 auto .9rem; max-width: 500px; }
    .enrollment-footer { align-items: center; background: #fff; border-top: 1px solid var(--integration-border); display: flex; gap: 1rem; justify-content: space-between; padding: .85rem 1rem; }
    .enrollment-pagination-summary { color: #7b8a9e; font-size: .72rem; }
    .enrollment-pagination-summary strong { color: #42526b; }
    .enrollment-footer nav { margin-left: auto; }
    .enrollment-footer .pagination { margin: 0; }
    .start-modal { --integration-purple: #4b11bd; --integration-purple-dark: #270078; overflow-y: hidden !important; }
    .start-modal .modal-dialog { align-items: center; display: flex; margin: 1rem auto; max-width: 880px; min-height: calc(100% - 2rem); width: calc(100% - 2rem); }
    .start-modal .modal-content { background: #fff; border: 0; border-radius: 18px; box-shadow: 0 28px 75px rgba(21, 26, 42, .34); max-height: calc(100vh - 2rem); max-height: calc(100dvh - 2rem); overflow: hidden; }
    .start-modal .modal-content > form { display: flex; flex-direction: column; max-height: inherit; min-height: 0; }
    body.start-enrollment-modal-open { overflow: hidden !important; }
    body.start-enrollment-modal-open .modal-backdrop.show { backdrop-filter: blur(5px); background: #273143; opacity: .62; }
    body.mblrc-interface .start-modal .modal-header { align-items: center; background: linear-gradient(115deg, #2a007b 0%, #3d079f 58%, #270073 100%); border: 0; display: flex; flex: 0 0 auto; min-height: 136px; padding: 1.5rem 2rem; }
    .start-modal-heading { align-items: center; display: flex; gap: 1.15rem; min-width: 0; }
    .start-modal-heading-icon { align-items: center; background: rgba(255, 255, 255, .11); border-radius: 50%; color: #e9ddff; display: flex; flex: 0 0 60px; font-size: 1.8rem; height: 60px; justify-content: center; width: 60px; }
    .start-modal .modal-title { color: #fff; font-size: 1.5rem; font-weight: 750; letter-spacing: -.025em; line-height: 1.2; margin: 0; }
    .start-modal-subtitle { color: rgba(255, 255, 255, .92); font-size: .88rem; line-height: 1.45; margin: .35rem 0 0; max-width: 620px; }
    .start-modal .modal-close { align-items: center; align-self: flex-start; background: rgba(255, 255, 255, .08); border: 0; border-radius: 50%; color: #fff; display: inline-flex; flex: 0 0 46px; font-size: 1.45rem; height: 46px; justify-content: center; margin-left: 1rem; opacity: 1; padding: 0; width: 46px; }
    .start-modal .modal-close:hover, .start-modal .modal-close:focus { background: rgba(255, 255, 255, .17); color: #fff; outline: 0; transform: scale(1.03); }
    .start-modal .modal-close:focus-visible { box-shadow: 0 0 0 4px rgba(255, 255, 255, .24); }
    .start-modal .modal-body { flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior: contain; padding: 1.5rem 2rem 1.65rem; scrollbar-gutter: stable; }
    .start-modal .modal-footer { background: #fff; border-top: 1px solid #e4e7ec; flex: 0 0 auto; padding: 1rem 2rem; }
    .modal-description { color: #637083; font-size: .9rem; line-height: 1.55; margin-bottom: 1.2rem; }
    .start-modal-intro { align-items: center; background: linear-gradient(90deg, #fbfaff, #f8f5ff); border: 1px solid #ded3fa; border-radius: 11px; color: #28243f; display: flex; font-size: .88rem; gap: .8rem; line-height: 1.45; margin-bottom: 1.25rem; padding: .8rem 1rem; }
    .start-modal-intro i { color: var(--integration-purple); flex: 0 0 auto; font-size: 1.65rem; }
    .start-modal .enrollment-label { color: #20283a; font-size: .85rem; font-weight: 750; margin-bottom: .45rem; }
    .start-modal .enrollment-label > span[aria-hidden="true"] { color: #ef3340; }
    .start-modal-control { position: relative; }
    .start-modal-control > i { color: var(--integration-purple); font-size: 1.45rem; left: 1.15rem; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; }
    .start-modal-control .control-chevron { left: auto; right: 1.15rem; }
    .start-modal-control .form-control { background: #fff; border: 1px solid #cfd6e1; border-radius: 9px; color: #273244; font-size: .9rem; height: 56px; padding: .65rem 3.2rem; }
    .start-modal-control .form-control::placeholder { color: #8a96a8; }
    .start-modal-control .form-control:focus { border-color: var(--integration-purple); box-shadow: 0 0 0 4px rgba(75, 17, 189, .13); outline: 0; }
    .start-modal-control input[type="search"]::-webkit-search-cancel-button { display: none; }
    .start-date-control .form-control { padding-right: 1rem; }
    .start-modal-field-help { color: #66758a; display: block; font-size: .78rem; line-height: 1.4; margin-top: .55rem; }
    .beneficiary-combobox { position: relative; }
    .beneficiary-results { background: #fff; border: 1px solid #d7dee8; border-radius: 10px; box-shadow: 0 12px 28px rgba(23, 43, 77, .18); left: 0; max-height: 220px; overflow-y: auto; position: absolute; right: 0; top: calc(100% + 5px); z-index: 1080; }
    .beneficiary-result { background: #fff; border: 0; border-bottom: 1px solid #edf1f6; color: #344563; display: block; padding: .8rem 1rem; text-align: left; width: 100%; }
    .beneficiary-result:last-child { border-bottom: 0; }
    .beneficiary-result:hover, .beneficiary-result:focus { background: #f5f3ff; outline: 0; }
    .beneficiary-result:focus-visible { box-shadow: inset 0 0 0 2px #8c80d5; }
    .beneficiary-result strong { display: block; font-size: .85rem; }
    .beneficiary-result small { color: #8492a6; display: block; font-size: .74rem; margin-top: .15rem; }
    .beneficiary-search-state { color: #7b8a9e; font-size: .8rem; padding: .9rem; text-align: center; }
    .beneficiary-preview { background: #f8f9fc; border: 1px solid #e2e7ef; border-radius: 9px; margin-top: .7rem; padding: .7rem; }
    .beneficiary-preview-title { color: #8795a8; font-size: .64rem; font-weight: 750; letter-spacing: .04em; margin-bottom: .55rem; text-transform: uppercase; }
    .beneficiary-preview-grid { display: grid; gap: .55rem .8rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .beneficiary-preview-grid span { color: #8795a8; display: block; font-size: .63rem; font-weight: 700; }
    .beneficiary-preview-grid strong { color: #405169; display: block; font-size: .75rem; margin-top: .16rem; }
    .eligibility-message { align-items: flex-start; border-radius: 7px; display: flex; font-size: .7rem; gap: .4rem; grid-column: 1 / -1; padding: .55rem .6rem; }
    .eligibility-message.is-eligible { background: #e7f7ef; color: #187a56; }
    .eligibility-message.is-ineligible { background: #fff3dc; color: #8a5b00; }
    .existing-enrollment { border-top: 1px solid #e1e7ef; grid-column: 1 / -1; padding-top: .65rem; }
    .existing-enrollment strong { color: #8a5b00; }
    .existing-enrollment-facts { display: flex; flex-wrap: wrap; gap: .35rem 1rem; margin: .45rem 0; }
    .existing-enrollment-facts div { min-width: 120px; }
    .expected-date { align-items: center; background: linear-gradient(100deg, #faf8ff, #f5f0ff); border: 1px solid #ded3fa; border-radius: 10px; display: flex; gap: .7rem; min-height: 56px; padding: .55rem .8rem; }
    .expected-date-icon { align-items: center; background: #eee6ff; border: 1px solid #d7c7ff; border-radius: 50%; color: var(--integration-purple); display: flex; flex: 0 0 36px; font-size: 1rem; height: 36px; justify-content: center; width: 36px; }
    .expected-date strong { color: var(--integration-purple); display: block; font-size: .85rem; }
    .expected-date small { color: #657187; display: block; font-size: .75rem; margin-top: .1rem; }
    .enrollment-assurance { align-items: center; background: #fffaf0; border: 1px solid #f2d99c; border-radius: 11px; color: #485267; display: flex; font-size: .78rem; gap: .8rem; line-height: 1.45; margin-top: 1.35rem; padding: .8rem 1rem; }
    .enrollment-assurance i { color: #ee9b00; flex: 0 0 auto; font-size: 1.6rem; }
    .enrollment-assurance strong { color: #303849; }
    .confirmation-card { background: #f8f9fc; border: 1px solid #e1e7ef; border-radius: 9px; padding: .85rem; }
    .confirmation-card dl { display: grid; gap: .7rem; grid-template-columns: 1fr 1fr; margin: 0; }
    .confirmation-card dt { color: #8795a8; font-size: .63rem; font-weight: 750; text-transform: uppercase; }
    .confirmation-card dd { color: #405169; font-size: .76rem; font-weight: 650; margin: .15rem 0 0; }
    .submission-guidance { color: #718096; font-size: .68rem; line-height: 1.4; margin: .65rem 0 0; }
    body.mblrc-interface .start-modal .start-enrollment-button:disabled { background: #e5def1; border-color: #d7cbe8; color: #89769f; cursor: not-allowed; opacity: 1; }
    .start-modal .modal-footer { align-items: center; justify-content: space-between; }
    .start-modal .modal-footer .filter-clear { border-color: #cfd6df; color: #4c5a70; font-size: .84rem; height: 50px; min-width: 145px; }
    .start-modal .modal-footer .filter-clear i { font-size: 1.2rem; }
    body.mblrc-interface .start-modal .modal-footer .start-enrollment-button { background: linear-gradient(105deg, #5515d6, #3d05ad); border: 0; box-shadow: 0 7px 16px rgba(67, 8, 176, .2); font-size: .9rem; height: 50px; justify-content: center; min-width: 300px; }
    body.mblrc-interface .start-modal .modal-footer .start-enrollment-button:hover, body.mblrc-interface .start-modal .modal-footer .start-enrollment-button:focus { background: linear-gradient(105deg, #4810bd, #31018f); box-shadow: 0 8px 20px rgba(67, 8, 176, .28); }
    .start-modal .modal-footer .start-enrollment-button i { font-size: 1.25rem; }
    .start-modal [data-review-enrollment] { display: inline-flex !important; }
    .field-error { color: #b42318; font-size: .76rem; margin-top: .4rem; }
    [data-hidden] { display: none !important; }
    @media (max-width: 991px) {
        .integration-summary { grid-template-columns: 1fr 1fr; }
        .enrollment-toolbar { grid-template-columns: 1fr 1fr; }
        .enrollment-field:first-child { grid-column: 1 / -1; }
        .enrollment-filter-actions { justify-content: flex-end; }
        .monitoring-facts { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 767px) {
        .integration-header { align-items: flex-start; flex-direction: column; }
        .start-enrollment-button { justify-content: center; width: 100%; }
        .enrollment-toolbar { grid-template-columns: 1fr; }
        .enrollment-field:first-child { grid-column: auto; }
        .enrollment-filter-actions { justify-content: stretch; }
        .filter-apply { flex: 1; }
        .enrollment-table, .enrollment-table tbody, .enrollment-table tr, .enrollment-table td { display: block; width: 100% !important; }
        .enrollment-table thead { display: none; }
        .enrollment-table tr.enrollment-row { border-bottom: 1px solid var(--integration-border); padding: .65rem 0; }
        .enrollment-table tbody td { align-items: flex-start; border: 0; display: flex; gap: .75rem; padding: .38rem 1rem; text-align: left !important; }
        .enrollment-table tbody td::before { color: #8a99ac; content: attr(data-label); flex: 0 0 92px; font-size: .67rem; font-weight: 700; letter-spacing: .03em; padding-top: .2rem; text-transform: uppercase; }
        .enrollment-table tbody td:last-child .enrollment-action { flex: 1; min-height: 42px; }
        .monitoring-panel-cell::before { display: none; }
        .monitoring-panel-cell { display: block !important; }
        .monitoring-facts { grid-template-columns: 1fr; }
        .monitoring-panel-header { display: block; }
        .enrollment-footer { align-items: flex-start; flex-direction: column; }
        .enrollment-footer nav { margin-left: 0; max-width: 100%; overflow-x: auto; }
        body.mblrc-interface .start-modal .modal-header { min-height: 120px; padding: 1.25rem; }
        .start-modal-heading { gap: 1rem; }
        .start-modal-heading-icon { flex-basis: 56px; font-size: 1.65rem; height: 56px; width: 56px; }
        .start-modal .modal-title { font-size: 1.35rem; }
        .start-modal-subtitle { font-size: .86rem; }
        .start-modal .modal-close { flex-basis: 44px; font-size: 1.35rem; height: 44px; margin-left: .5rem; width: 44px; }
        .beneficiary-preview-grid { grid-template-columns: 1fr 1fr; }
        .start-modal .modal-body { padding: 1.2rem 1.25rem 1.35rem; }
        .start-modal .modal-footer { padding: .85rem 1.25rem; }
        .start-modal-intro, .enrollment-assurance { font-size: .84rem; }
        body.mblrc-interface .start-modal .modal-footer .start-enrollment-button { min-width: 250px; }
    }
    @media (max-width: 480px) {
        .integration-summary { grid-template-columns: 1fr; }
        .beneficiary-preview-grid, .confirmation-card dl { grid-template-columns: 1fr; }
        .start-modal .modal-dialog { margin: .5rem; min-height: calc(100% - 1rem); width: calc(100% - 1rem); }
        .start-modal .modal-content { max-height: calc(100vh - 1rem); max-height: calc(100dvh - 1rem); }
        body.mblrc-interface .start-modal .modal-header { align-items: flex-start; padding: 1.25rem; }
        .start-modal-heading-icon { display: none; }
        .start-modal .modal-close { flex-basis: 40px; height: 40px; width: 40px; }
        .start-modal .modal-body { padding: 1.25rem; }
        .start-modal .modal-footer { padding: 1rem 1.25rem; }
        .start-modal-control .form-control { height: 54px; }
        .start-modal .modal-footer { align-items: stretch; flex-direction: column-reverse; }
        .start-modal .modal-footer button { min-height: 44px; width: 100%; }
        body.mblrc-interface .start-modal .modal-footer .start-enrollment-button { min-width: 0; }
    }
    @media (max-height: 760px) {
        body.mblrc-interface .start-modal .modal-header { min-height: 104px; padding-bottom: 1rem; padding-top: 1rem; }
        .start-modal-heading-icon { flex-basis: 48px; font-size: 1.45rem; height: 48px; width: 48px; }
        .start-modal .modal-title { font-size: 1.25rem; }
        .start-modal-subtitle { font-size: .78rem; }
        .start-modal .modal-body { padding-bottom: 1.1rem; padding-top: 1.1rem; }
    }
</style>
@endpush

@section('content')
<div class="integration-page">
    <header class="integration-header" aria-labelledby="integration-page-title">
        <div class="integration-title-main"><span class="module-title-icon"><i class="mdi mdi-progress-clock" aria-hidden="true"></i></span><div><h2 id="integration-page-title">Integration Monitoring</h2><p>Manage assigned beneficiaries undergoing the official three-month integration period.</p></div></div>
        <button type="button" class="start-enrollment-button" data-bs-toggle="modal" data-bs-target="#startEnrollmentModal"><i class="mdi mdi-plus" aria-hidden="true"></i>Start Enrollment</button>
    </header>

    <section class="integration-summary" aria-label="Assigned integration enrollment summary">
        <div class="summary-card summary-assigned"><div class="summary-icon"><i class="mdi mdi-account-group" aria-hidden="true"></i></div><div><strong>{{ number_format($summary['assigned']) }}</strong><span>Assigned</span></div></div>
        <div class="summary-card summary-active"><div class="summary-icon"><i class="mdi mdi-progress-check" aria-hidden="true"></i></div><div><strong>{{ number_format($summary['active']) }}</strong><span>Active</span></div></div>
        <div class="summary-card summary-completed"><div class="summary-icon"><i class="mdi mdi-check-decagram" aria-hidden="true"></i></div><div><strong>{{ number_format($summary['completed']) }}</strong><span>Completed</span></div></div>
        <div class="summary-card summary-attention"><div class="summary-icon"><i class="mdi mdi-alert-circle" aria-hidden="true"></i></div><div><strong>{{ number_format($summary['attention']) }}</strong><span>Needs Attention</span></div></div>
    </section>

    <section class="card enrollment-card" aria-labelledby="assigned-enrollments-title">
        <form method="GET" action="{{ route('mblrc.enrollments.index') }}" class="enrollment-toolbar" role="search">
            <div class="enrollment-field"><label for="enrollment-search" class="enrollment-label">Search assigned enrollments</label><div class="enrollment-search"><i class="mdi mdi-magnify" aria-hidden="true"></i><input id="enrollment-search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" class="form-control" placeholder="Search FR/FVE identifier" autocomplete="off"></div></div>
            <div class="enrollment-field"><label for="enrollment-status" class="enrollment-label">Status</label><select id="enrollment-status" name="status" class="form-control"><option value="">All statuses</option><option value="in_progress" @selected(($filters['status'] ?? '') === 'in_progress')>In Progress</option><option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option><option value="attention" @selected(($filters['status'] ?? '') === 'attention')>Needs Attention</option></select></div>
            <div class="enrollment-field"><label for="enrollment-sort" class="enrollment-label">Sort</label><select id="enrollment-sort" name="sort" class="form-control"><option value="recently_updated" @selected(($filters['sort'] ?? 'recently_updated') === 'recently_updated')>Recently updated</option><option value="newest_started" @selected(($filters['sort'] ?? '') === 'newest_started')>Newest started</option><option value="oldest_started" @selected(($filters['sort'] ?? '') === 'oldest_started')>Oldest started</option></select></div>
            <div class="enrollment-filter-actions">@if($hasActiveFilters)<a href="{{ route('mblrc.enrollments.index') }}" class="filter-clear"><i class="mdi mdi-close" aria-hidden="true"></i>Clear</a>@endif<button type="submit" class="filter-apply"><i class="mdi mdi-filter-outline" aria-hidden="true"></i>Apply</button></div>
        </form>

        <div class="enrollment-section-heading"><h3 id="assigned-enrollments-title">Assigned Enrollments</h3><span class="enrollment-result-count">{{ number_format($enrollments->total()) }} {{ Str::plural('enrollment', $enrollments->total()) }}</span></div>

        @if($enrollments->isNotEmpty())
            <div class="table-responsive">
                <table class="table enrollment-table">
                    <caption class="sr-only">Integration enrollments assigned to the authenticated MBLRC user</caption>
                    <thead><tr><th>Beneficiary</th><th>Monitoring period</th><th>Status</th><th>Progress</th><th>LSWDO referral</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($enrollments as $enrollment)
                            @php
                                $expected = $enrollment->expectedCompletionDate();
                                $attention = $enrollment->needsAttention();
                                $panelLabel = $enrollment->status === 'completed' ? 'View Summary' : ($attention ? 'Complete Monitoring' : 'View Monitoring');
                            @endphp
                            <tr class="enrollment-row" id="enrollment-{{ $enrollment->id }}">
                                <td data-label="Beneficiary"><span class="beneficiary-id">{{ $enrollment->formerRebel->classified_id }}</span><span class="enrollment-secondary">{{ $enrollment->formerRebel->municipality?->name ?? 'Municipality not recorded' }}</span></td>
                                <td data-label="Monitoring period"><span class="period-primary">{{ $enrollment->integration_started_at?->format('M d') ?? '—' }} – {{ $expected?->format('M d, Y') ?? '—' }}</span><span class="enrollment-secondary">Started {{ $enrollment->integration_started_at?->format('M d, Y') ?? 'not recorded' }}</span></td>
                                <td data-label="Status"><x-enrollment-status-badge :enrollment="$enrollment" /></td>
                                <td data-label="Progress"><x-enrollment-progress :enrollment="$enrollment" /></td>
                                <td data-label="LSWDO referral">
                                    @if($enrollment->referral)
                                        <span class="referral-status">{{ str($enrollment->referral->status)->title() }}</span>
                                        @if($enrollment->referral->eclipCase && auth()->user()->can('view', $enrollment->referral->eclipCase))<a class="enrollment-secondary referral-link" href="{{ route('mblrc.eclip.show', $enrollment->referral->eclipCase) }}">{{ $enrollment->referral->referral_number }} <span aria-hidden="true">↗</span></a>@else<span class="enrollment-secondary">{{ $enrollment->referral->referral_number }}</span>@endif
                                    @else
                                        <span class="referral-status">No referral</span><span class="enrollment-secondary">Created after verified completion</span>
                                    @endif
                                </td>
                                <td data-label="Action"><button type="button" class="enrollment-action @if($attention) attention @endif" data-bs-toggle="collapse" data-bs-target="#monitoring-{{ $enrollment->id }}" aria-expanded="false" aria-controls="monitoring-{{ $enrollment->id }}"><i class="mdi {{ $enrollment->status === 'completed' ? 'mdi-clipboard-check-outline' : 'mdi-clipboard-text-outline' }}" aria-hidden="true"></i>{{ $panelLabel }}</button></td>
                            </tr>
                            <tr><td colspan="6" class="monitoring-panel-cell"><div class="collapse" id="monitoring-{{ $enrollment->id }}"><div class="monitoring-panel">
                                <div class="monitoring-panel-header"><div><h4>{{ $enrollment->formerRebel->classified_id }} Integration Record</h4><p>Three-month monitoring and verified LSWDO handoff information.</p></div><x-enrollment-status-badge :enrollment="$enrollment" /></div>
                                <div class="monitoring-facts">
                                    <div class="monitoring-fact"><span>Started</span><strong>{{ $enrollment->integration_started_at?->format('M d, Y') ?? 'Not recorded' }}</strong></div>
                                    <div class="monitoring-fact"><span>Expected completion</span><strong>{{ $expected?->format('M d, Y') ?? 'Not available' }}</strong></div>
                                    <div class="monitoring-fact"><span>Last updated</span><strong>{{ $enrollment->updated_at?->timezone(config('app.display_timezone'))->format('M d, Y · h:i A') }}</strong></div>
                                    <div class="monitoring-fact"><span>Referral</span><strong>{{ $enrollment->referral ? str($enrollment->referral->status)->title() : 'Not created' }}</strong></div>
                                </div>
                                @if($enrollment->status === 'in_progress' && $attention)
                                    <div class="completion-note"><i class="mdi mdi-information-outline" aria-hidden="true"></i><span>The official three-month period has elapsed. Verify completion evidence and location before creating the LSWDO referral.</span></div>
                                    <form method="POST" action="{{ route('mblrc.enrollments.complete', $enrollment) }}" class="completion-form" data-prevent-double-submit>
                                        @csrf
                                        <input type="hidden" name="enrollment_id" value="{{ $enrollment->id }}">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label" for="completion-date-{{ $enrollment->id }}">Completion date <span aria-hidden="true">*</span></label><input id="completion-date-{{ $enrollment->id }}" type="date" name="integration_completed_at" class="form-control" min="{{ $expected?->toDateString() }}" max="{{ now(config('app.display_timezone'))->toDateString() }}" value="{{ old('enrollment_id') == $enrollment->id ? old('integration_completed_at') : '' }}" required></div>
                                            <div class="col-md-6"><label class="form-label" for="verified-municipality-{{ $enrollment->id }}">Verified municipality <span aria-hidden="true">*</span></label><select id="verified-municipality-{{ $enrollment->id }}" name="verified_municipality_id" class="form-select" required><option value="">Select municipality</option>@foreach($municipalities as $municipality)<option value="{{ $municipality->id }}" @selected(old('enrollment_id') == $enrollment->id && old('verified_municipality_id') == $municipality->id)>{{ $municipality->name }}</option>@endforeach</select></div>
                                            <div class="col-md-6"><label class="form-label" for="intention-source-{{ $enrollment->id }}">Intention source <span aria-hidden="true">*</span></label><input id="intention-source-{{ $enrollment->id }}" name="phase_one_evidence[intention_to_surface][source]" class="form-control" value="{{ old('enrollment_id') == $enrollment->id ? old('phase_one_evidence.intention_to_surface.source') : '' }}" maxlength="100" required></div>
                                            <div class="col-md-6"><label class="form-label" for="intention-record-{{ $enrollment->id }}">Intention source record <span aria-hidden="true">*</span></label><input id="intention-record-{{ $enrollment->id }}" name="phase_one_evidence[intention_to_surface][source_record]" class="form-control" value="{{ old('enrollment_id') == $enrollment->id ? old('phase_one_evidence.intention_to_surface.source_record') : '' }}" maxlength="255" required></div>
                                            <div class="col-md-6"><label class="form-label" for="coordination-source-{{ $enrollment->id }}">Coordination source <span aria-hidden="true">*</span></label><input id="coordination-source-{{ $enrollment->id }}" name="phase_one_evidence[receiving_unit_coordination][source]" class="form-control" value="{{ old('enrollment_id') == $enrollment->id ? old('phase_one_evidence.receiving_unit_coordination.source') : '' }}" maxlength="100" required></div>
                                            <div class="col-md-6"><label class="form-label" for="coordination-record-{{ $enrollment->id }}">Coordination source record <span aria-hidden="true">*</span></label><input id="coordination-record-{{ $enrollment->id }}" name="phase_one_evidence[receiving_unit_coordination][source_record]" class="form-control" value="{{ old('enrollment_id') == $enrollment->id ? old('phase_one_evidence.receiving_unit_coordination.source_record') : '' }}" maxlength="255" required></div>
                                            <div class="col-12"><label class="form-label" for="location-remarks-{{ $enrollment->id }}">Location verification remarks</label><textarea id="location-remarks-{{ $enrollment->id }}" name="location_verification_remarks" class="form-control" rows="2" maxlength="5000">{{ old('enrollment_id') == $enrollment->id ? old('location_verification_remarks') : '' }}</textarea></div>
                                            @if(old('enrollment_id') == $enrollment->id && $errors->any())<div class="col-12"><div class="alert alert-danger py-2 mb-0" role="alert">{{ $errors->first() }}</div></div>@endif
                                            <div class="col-12 text-right"><button type="submit" class="btn btn-success"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i>Verify Completion &amp; Create Referral</button></div>
                                        </div>
                                    </form>
                                @elseif($enrollment->status === 'in_progress')
                                    <div class="monitoring-notice"><i class="mdi mdi-progress-clock" aria-hidden="true"></i><span>Monitoring is active. Completion verification becomes available on {{ $expected?->format('M d, Y') }} after the official three-calendar-month period.</span></div>
                                @else
                                    <div class="monitoring-notice"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i><span>Monitoring was verified complete on {{ $enrollment->integration_completed_at?->format('M d, Y') ?? 'an unrecorded date' }}. {{ $enrollment->referral ? 'The LSWDO referral is '.str($enrollment->referral->status)->lower().'.' : 'No referral is recorded.' }}</span></div>
                                @endif
                            </div></div></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="enrollment-empty"><div class="enrollment-empty-icon"><i class="mdi mdi-clipboard-text-outline" aria-hidden="true"></i></div><strong>{{ $hasActiveFilters ? 'No integration enrollments found' : 'No integration enrollments yet' }}</strong><p>{{ $hasActiveFilters ? 'No assigned enrollment matches the selected search, status, or sorting criteria.' : 'Eligible FR/FVE beneficiaries will appear here after their official three-month monitoring period begins.' }}</p>@if($hasActiveFilters)<a href="{{ route('mblrc.enrollments.index') }}" class="filter-clear">Clear filters</a>@else<button type="button" class="start-enrollment-button" data-bs-toggle="modal" data-bs-target="#startEnrollmentModal"><i class="mdi mdi-plus" aria-hidden="true"></i>Start Enrollment</button>@endif</div>
        @endif

        @if($enrollments->isNotEmpty())<footer class="enrollment-footer"><div class="enrollment-pagination-summary">Showing <strong>{{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }}</strong> of <strong>{{ number_format($enrollments->total()) }}</strong> enrollments</div>@if($enrollments->hasPages()){{ $enrollments->onEachSide(1)->links() }}@endif</footer>@endif
    </section>
</div>

<div class="modal fade start-modal" id="startEnrollmentModal" tabindex="-1" role="dialog" aria-labelledby="start-enrollment-title" aria-describedby="start-enrollment-description" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content">
        <form method="POST" action="{{ route('mblrc.enrollments.store') }}" data-enrollment-form data-prevent-double-submit>
            @csrf
            <div class="modal-header">
                <div class="start-modal-heading">
                    <span class="start-modal-heading-icon"><i class="mdi mdi-clipboard-account-outline" aria-hidden="true"></i></span>
                    <div><h3 class="modal-title" id="start-enrollment-title">Start Integration Enrollment</h3><p class="start-modal-subtitle" id="start-enrollment-description">Begin the official three-month integration monitoring for an eligible FR/FVE beneficiary.</p></div>
                </div>
                <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close Start Integration Enrollment dialog"><i class="mdi mdi-close" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <div data-enrollment-entry>
                    <div class="start-modal-intro"><i class="mdi mdi-information-outline" aria-hidden="true"></i><span>Select an eligible FR/FVE beneficiary to begin the official three-month integration monitoring period.</span></div>
                    <div class="mb-3 beneficiary-combobox">
                        <label class="enrollment-label" for="beneficiary-search">FR/FVE Beneficiary <span aria-hidden="true">*</span><span class="sr-only"> required</span></label>
                        <div class="start-modal-control"><i class="mdi mdi-account" aria-hidden="true"></i><input id="beneficiary-search" type="search" class="form-control" placeholder="Search beneficiary or FR/FVE identifier..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="beneficiary-results" aria-expanded="false" aria-required="true" @error('former_rebel_id') aria-invalid="true" aria-describedby="beneficiary-error" @enderror data-beneficiary-search><i class="mdi mdi-chevron-down control-chevron" aria-hidden="true"></i></div>
                        <input type="hidden" name="former_rebel_id" value="{{ old('former_rebel_id') }}" data-beneficiary-id required>
                        <div id="beneficiary-results" class="beneficiary-results" role="listbox" data-beneficiary-results hidden></div>
                        @error('former_rebel_id')<div class="field-error" id="beneficiary-error" role="alert">{{ $message }}</div>@enderror
                    </div>
                    <div class="beneficiary-preview" data-beneficiary-preview hidden>
                        <div class="beneficiary-preview-title">Selected Beneficiary</div>
                        <div class="beneficiary-preview-grid"><div><span>Identifier</span><strong data-preview-identifier>—</strong></div><div><span>Municipality</span><strong data-preview-municipality>—</strong></div><div><span>Current Integration Enrollment</span><strong data-current-enrollment>None</strong></div><div class="eligibility-message" data-preview-eligibility role="status"><i class="mdi mdi-check-circle-outline" aria-hidden="true"></i><span></span></div><div class="existing-enrollment" data-existing-enrollment hidden><strong data-existing-heading>Active enrollment already exists</strong><div class="existing-enrollment-facts"><div><span>Started</span><strong data-existing-start>—</strong></div><div><span>Expected Completion</span><strong data-existing-expected>—</strong></div></div><a href="#" class="referral-link" data-existing-link hidden>View Existing Enrollment</a></div></div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-sm-6"><label class="enrollment-label" for="integration_started_at">Monitoring Start Date <span aria-hidden="true">*</span><span class="sr-only"> required</span></label><div class="start-modal-control start-date-control"><i class="mdi mdi-calendar-blank-outline" aria-hidden="true"></i><input class="form-control" type="date" id="integration_started_at" name="integration_started_at" value="{{ old('integration_started_at') }}" max="{{ now(config('app.display_timezone'))->toDateString() }}" required @error('integration_started_at') aria-invalid="true" aria-describedby="start-date-error" @enderror data-start-date></div><small class="start-modal-field-help">Select the date monitoring will officially begin.</small>@error('integration_started_at')<div class="field-error" id="start-date-error" role="alert">{{ $message }}</div>@enderror</div>
                        <div class="col-sm-6"><span class="enrollment-label">Expected Completion</span><div class="expected-date" aria-live="polite"><span class="expected-date-icon"><i class="mdi mdi-calendar-blank-outline" aria-hidden="true"></i></span><div><strong data-expected-date>Choose a start date</strong><small>Calculated as three calendar months</small></div></div><small class="start-modal-field-help">Completion date will be calculated automatically.</small></div>
                    </div>
                    <div class="enrollment-assurance"><i class="mdi mdi-lightbulb-on-outline" aria-hidden="true"></i><span><strong>Please ensure:</strong> The selected beneficiary is eligible and all initial requirements are verified before starting the enrollment.</span></div>
                    <div class="sr-only" data-start-notice hidden>This will begin the beneficiary’s official three-month integration monitoring period.</div>
                    <p class="submission-guidance sr-only" data-submission-guidance aria-live="polite">Select an eligible beneficiary and monitoring start date to continue.</p>
                </div>
                <div data-enrollment-confirmation hidden>
                    <p class="modal-description">Confirm the official monitoring period before creating this enrollment.</p>
                    <div class="confirmation-card"><dl><div><dt>Beneficiary</dt><dd data-confirm-identifier>—</dd></div><div><dt>Start date</dt><dd data-confirm-start>—</dd></div><div><dt>Expected completion</dt><dd data-confirm-expected>—</dd></div><div><dt>Duration</dt><dd>Three calendar months</dd></div></dl></div>
                    <div class="monitoring-notice mt-3"><i class="mdi mdi-information-outline" aria-hidden="true"></i><span>This begins the beneficiary’s official three-month integration monitoring period.</span></div>
                </div>
            </div>
            <div class="modal-footer" data-entry-actions><button type="button" class="filter-clear" data-bs-dismiss="modal"><i class="mdi mdi-close" aria-hidden="true"></i>Cancel</button><button type="button" class="start-enrollment-button" data-review-enrollment disabled><i class="mdi mdi-play-circle" aria-hidden="true"></i>Start Enrollment</button></div>
            <div class="modal-footer" data-confirm-actions hidden><button type="button" class="filter-clear" data-edit-enrollment><i class="mdi mdi-arrow-left" aria-hidden="true"></i>Back</button><button type="submit" class="start-enrollment-button" data-confirm-enrollment><i class="mdi mdi-check-circle" aria-hidden="true"></i><span>Confirm Enrollment</span></button></div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('startEnrollmentModal');
    var form = document.querySelector('[data-enrollment-form]');
    var search = document.querySelector('[data-beneficiary-search]');
    var results = document.querySelector('[data-beneficiary-results]');
    var beneficiaryId = document.querySelector('[data-beneficiary-id]');
    var preview = document.querySelector('[data-beneficiary-preview]');
    var startDate = document.querySelector('[data-start-date]');
    var expectedOutput = document.querySelector('[data-expected-date]');
    var reviewButton = document.querySelector('[data-review-enrollment]');
    var guidance = document.querySelector('[data-submission-guidance]');
    var startNotice = document.querySelector('[data-start-notice]');
    var selected = null;
    var expectedDate = null;
    var timer = null;
    var activeResult = -1;

    function formatDate(value) {
        if (!value) return 'Not available';
        var parts = value.split('-').map(Number);
        return new Intl.DateTimeFormat('en-US', { month: 'short', day: '2-digit', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(parts[0], parts[1] - 1, parts[2])));
    }
    function addMonthsNoOverflow(value, months) {
        if (!value) return null;
        var parts = value.split('-').map(Number);
        var first = new Date(Date.UTC(parts[0], parts[1] - 1 + months, 1));
        var lastDay = new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth() + 1, 0)).getUTCDate();
        var date = new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth(), Math.min(parts[2], lastDay)));
        return date.toISOString().slice(0, 10);
    }
    function syncReviewState() {
        expectedDate = addMonthsNoOverflow(startDate.value, 3);
        expectedOutput.textContent = expectedDate ? formatDate(expectedDate) : 'Choose a start date';
        var ready = Boolean(selected && selected.eligible && startDate.value && expectedDate);
        reviewButton.disabled = !ready;
        startNotice.hidden = !ready;
        if (!selected) guidance.textContent = 'Select an eligible beneficiary and monitoring start date to continue.';
        else if (!selected.eligible) guidance.textContent = selected.eligibility_message;
        else if (!startDate.value) guidance.textContent = 'Choose a monitoring start date to continue.';
        else guidance.textContent = 'Ready to review and start enrollment.';
    }
    function hideResults() {
        results.hidden = true;
        search.setAttribute('aria-expanded', 'false');
        activeResult = -1;
        search.removeAttribute('aria-activedescendant');
    }
    function showState(message) {
        results.replaceChildren();
        var state = document.createElement('div');
        state.className = 'beneficiary-search-state';
        state.textContent = message;
        results.appendChild(state);
        results.hidden = false;
        search.setAttribute('aria-expanded', 'true');
    }
    function selectBeneficiary(item) {
        selected = item;
        beneficiaryId.value = item.id;
        search.value = item.classified_id;
        document.querySelector('[data-preview-identifier]').textContent = item.classified_id;
        document.querySelector('[data-preview-municipality]').textContent = item.municipality || 'Not recorded';
        document.querySelector('[data-current-enrollment]').textContent = item.has_existing_enrollment ? (item.existing_enrollment ? item.existing_enrollment.status.replace('_', ' ').replace(/\b\w/g, function (letter) { return letter.toUpperCase(); }) : 'Exists outside your assigned list') : 'None';
        var eligibility = document.querySelector('[data-preview-eligibility]');
        eligibility.classList.toggle('is-eligible', item.eligible);
        eligibility.classList.toggle('is-ineligible', !item.eligible);
        eligibility.querySelector('i').className = 'mdi ' + (item.eligible ? 'mdi-check-circle-outline' : 'mdi-alert-circle-outline');
        eligibility.querySelector('span').textContent = item.eligibility_message;
        var existing = document.querySelector('[data-existing-enrollment]');
        existing.hidden = !item.existing_enrollment;
        if (item.existing_enrollment) {
            document.querySelector('[data-existing-heading]').textContent = item.existing_enrollment.status === 'in_progress' ? 'Active enrollment already exists' : 'Integration monitoring already completed';
            document.querySelector('[data-existing-start]').textContent = formatDate(item.existing_enrollment.started_at);
            document.querySelector('[data-existing-expected]').textContent = formatDate(item.existing_enrollment.expected_completion_at);
            var link = document.querySelector('[data-existing-link]');
            link.href = '#enrollment-' + item.existing_enrollment.id;
            link.hidden = false;
        }
        preview.hidden = false;
        hideResults();
        syncReviewState();
    }
    function renderResults(items) {
        results.replaceChildren();
        if (!items.length) return showState('No matching FR/FVE beneficiary found.');
        items.forEach(function (item, index) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'beneficiary-result';
            button.setAttribute('role', 'option');
            button.id = 'beneficiary-result-' + index;
            var identifier = document.createElement('strong');
            identifier.textContent = item.classified_id;
            var detail = document.createElement('small');
            detail.textContent = (item.municipality || 'Municipality not recorded') + (item.eligible ? ' · Eligible' : ' · Already enrolled');
            button.append(identifier, detail);
            button.addEventListener('click', function () { selectBeneficiary(item); });
            results.appendChild(button);
        });
        results.hidden = false;
        search.setAttribute('aria-expanded', 'true');
    }
    search.addEventListener('input', function () {
        selected = null;
        beneficiaryId.value = '';
        preview.hidden = true;
        syncReviewState();
        clearTimeout(timer);
        var query = search.value.trim();
        if (query.length < 2) return hideResults();
        timer = setTimeout(function () {
            showState('Searching authorized beneficiary records…');
            fetch(@js(route('mblrc.enrollments.beneficiaries')) + '?search=' + encodeURIComponent(query), { headers: { Accept: 'application/json' } })
                .then(function (response) { if (!response.ok) throw new Error(); return response.json(); })
                .then(renderResults)
                .catch(function () { showState('Beneficiary search is temporarily unavailable.'); });
        }, 250);
    });
    search.addEventListener('keydown', function (event) {
        var options = Array.from(results.querySelectorAll('[role="option"]'));
        if (event.key === 'Escape') return hideResults();
        if (!options.length || !['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) return;
        event.preventDefault();
        if (event.key === 'Enter' && activeResult >= 0) return options[activeResult].click();
        activeResult = event.key === 'ArrowUp'
            ? (activeResult <= 0 ? options.length - 1 : activeResult - 1)
            : (activeResult + 1) % options.length;
        options.forEach(function (option, index) { option.setAttribute('aria-selected', index === activeResult ? 'true' : 'false'); });
        search.setAttribute('aria-activedescendant', options[activeResult].id);
        options[activeResult].scrollIntoView({ block: 'nearest' });
    });
    startDate.addEventListener('change', syncReviewState);
    reviewButton.addEventListener('click', function () {
        if (!selected || !selected.eligible || !startDate.value) return;
        document.querySelector('[data-confirm-identifier]').textContent = selected.classified_id;
        document.querySelector('[data-confirm-start]').textContent = formatDate(startDate.value);
        document.querySelector('[data-confirm-expected]').textContent = formatDate(expectedDate);
        document.querySelector('[data-enrollment-entry]').hidden = true;
        document.querySelector('[data-entry-actions]').hidden = true;
        document.querySelector('[data-enrollment-confirmation]').hidden = false;
        document.querySelector('[data-confirm-actions]').hidden = false;
    });
    document.querySelector('[data-edit-enrollment]').addEventListener('click', function () {
        document.querySelector('[data-enrollment-entry]').hidden = false;
        document.querySelector('[data-entry-actions]').hidden = false;
        document.querySelector('[data-enrollment-confirmation]').hidden = true;
        document.querySelector('[data-confirm-actions]').hidden = true;
        startDate.focus();
    });
    document.querySelector('[data-existing-link]').addEventListener('click', function () {
        var instance = bootstrap.Modal.getInstance(modal);
        if (instance) instance.hide();
    });
    document.addEventListener('click', function (event) { if (!event.target.closest('.beneficiary-combobox')) hideResults(); });
    document.querySelectorAll('[data-prevent-double-submit]').forEach(function (target) { target.addEventListener('submit', function () { var button = target.querySelector('button[type="submit"]'); if (button) { button.disabled = true; button.setAttribute('aria-disabled', 'true'); var label = button.querySelector('span'); if (label) label.textContent = 'Starting Enrollment...'; } }); });
    modal.addEventListener('show.bs.modal', function () { document.body.classList.add('start-enrollment-modal-open'); });
    modal.addEventListener('hidden.bs.modal', function () { document.body.classList.remove('start-enrollment-modal-open'); });
    modal.addEventListener('shown.bs.modal', function () { search.focus(); });
    syncReviewState();

    @if($errors->has('former_rebel_id') || $errors->has('integration_started_at'))
        new bootstrap.Modal(modal).show();
        @if(old('former_rebel_id'))
            fetch(@js(route('mblrc.enrollments.beneficiaries')) + '?beneficiary_id=' + encodeURIComponent(@js(old('former_rebel_id'))), { headers: { Accept: 'application/json' } })
                .then(function (response) { if (!response.ok) throw new Error(); return response.json(); })
                .then(function (items) { if (items.length) selectBeneficiary(items[0]); });
        @endif
    @endif
    @if(old('enrollment_id'))
        var completionPanel = document.getElementById(@js('monitoring-'.old('enrollment_id')));
        if (completionPanel) new bootstrap.Collapse(completionPanel, { toggle: true });
    @endif
});
</script>
@endpush
