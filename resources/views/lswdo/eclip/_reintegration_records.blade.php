@php
    $hasOfficialWorkflow = $case->workflowActivities->isNotEmpty();
    $step10 = $case->workflowActivities->firstWhere('step_code', '10');
    $step12 = $case->workflowActivities->firstWhere('step_code', '12');
    $canAddPlan = ! $hasOfficialWorkflow || ($step10 && in_array($step10->status, ['pending', 'ongoing', 'late', 'returned_for_correction'], true));
    $canAddAlternate = ! $hasOfficialWorkflow || ($step12 && in_array($step12->status, ['pending', 'ongoing', 'late', 'returned_for_correction'], true));
@endphp

<section class="card review-card mb-4" aria-labelledby="reintegration-plan-title">
    <div class="card-body">
        <div class="sidebar-card-header"><div class="section-icon"><i class="mdi mdi-clipboard-list-outline"></i></div><div><h3 id="reintegration-plan-title" class="section-title">Reintegration Plan Items</h3><p class="section-subtitle">Step 10 needs, assistance commitments, responsible agencies, target dates, and final status.</p></div></div>
        @if($case->hasActiveParticipant(auth()->user(), 'case_processor') && $canAddPlan)
            <details class="sidebar-document-item mt-3" @if($errors->hasAny(['identified_need','proposed_assistance','responsible_agency','form_of_assistance'])) open @endif><summary class="sidebar-document-heading"><strong>Add plan item</strong><i class="mdi mdi-plus-circle-outline"></i></summary>
                <form method="POST" action="{{ route('lswdo.eclip.reintegration-plan-items.store', $case) }}" class="mt-3">@csrf
                    <div class="form-group"><label class="step-input-label" for="plan-need">Identified need</label><textarea id="plan-need" name="identified_need" class="form-control" rows="2" maxlength="5000" required>{{ old('identified_need') }}</textarea></div>
                    <div class="form-group"><label class="step-input-label" for="plan-assistance">Proposed assistance</label><input id="plan-assistance" name="proposed_assistance" value="{{ old('proposed_assistance') }}" class="form-control" maxlength="255" required></div>
                    <div class="form-group"><label class="step-input-label" for="plan-agency">Responsible agency</label><input id="plan-agency" name="responsible_agency" value="{{ old('responsible_agency') }}" class="form-control" maxlength="255" required></div>
                    <div class="form-group"><label class="step-input-label" for="plan-counterpart">LGU counterpart</label><input id="plan-counterpart" name="lgu_counterpart" value="{{ old('lgu_counterpart') }}" class="form-control" maxlength="255"></div>
                    <div class="form-group"><label class="step-input-label" for="plan-form">Form of assistance</label><input id="plan-form" name="form_of_assistance" value="{{ old('form_of_assistance') }}" class="form-control" maxlength="255" required></div>
                    <div class="row"><div class="form-group col-6"><label class="step-input-label" for="plan-amount">Amount/value</label><input id="plan-amount" name="amount" value="{{ old('amount') }}" class="form-control" type="number" min="0" step="0.01"></div><div class="form-group col-6"><label class="step-input-label" for="plan-target">Target date</label><input id="plan-target" name="target_date" value="{{ old('target_date') }}" class="form-control" type="date" required></div></div>
                    <input type="hidden" name="status" value="planned">
                    <div class="form-group"><label class="step-input-label" for="plan-partners">Partner agencies consulted</label><textarea id="plan-partners" name="partner_agencies" class="form-control" rows="2" maxlength="5000">{{ old('partner_agencies') }}</textarea></div>
                    <div class="form-group"><label class="step-input-label" for="plan-commitments">Agency commitments</label><textarea id="plan-commitments" name="agency_commitments" class="form-control" rows="2" maxlength="5000">{{ old('agency_commitments') }}</textarea></div>
                    <button class="btn btn-sm btn-outline-primary btn-block">Add reintegration plan item</button>
                </form>
            </details>
        @endif
        <div class="sidebar-document-list mt-3">@forelse($case->reintegrationPlanItems as $item)<details class="sidebar-document-item"><summary class="sidebar-document-heading"><strong>{{ $item->proposed_assistance }}</strong><span class="document-status status-pending">{{ str($item->status)->replace('_',' ')->title() }}</span></summary><small class="text-muted d-block mt-1">{{ $item->responsible_agency }} · {{ $item->form_of_assistance }} · Target {{ $item->target_date?->format('M d, Y') }}</small><p class="history-remarks mt-2 mb-0">{{ $item->identified_need }}</p><small class="text-muted d-block mt-2">{{ $item->interventions->count() }} linked intervention(s)</small></details>@empty<div class="no-document"><i class="mdi mdi-clipboard-alert-outline"></i>No reintegration plan items recorded.</div>@endforelse</div>
    </div>
</section>

<section class="card review-card mb-4" aria-labelledby="alternate-livelihood-title">
    <div class="card-body">
        <div class="sidebar-card-header"><div class="section-icon"><i class="mdi mdi-account-switch-outline"></i></div><div><h3 id="alternate-livelihood-title" class="section-title">Alternate Livelihood Beneficiary</h3><p class="section-subtitle">Step 12 applies only when the FR/FVE cannot directly implement the livelihood project.</p></div></div>
        @if($case->hasActiveParticipant(auth()->user(), 'case_processor') && $canAddAlternate)
            <details class="sidebar-document-item mt-3"><summary class="sidebar-document-heading"><strong>Record identified beneficiary</strong><i class="mdi mdi-plus-circle-outline"></i></summary>
                <form method="POST" action="{{ route('lswdo.eclip.livelihood-assistances.store', $case) }}" class="mt-3">@csrf
                    <div class="form-group"><label class="step-input-label" for="alternate-reason">Why the FR/FVE cannot directly implement</label><textarea id="alternate-reason" name="implementation_reason" class="form-control" rows="2" maxlength="5000" required></textarea></div>
                    <div class="form-group"><label class="step-input-label" for="alternate-name">Identified beneficiary</label><input id="alternate-name" name="beneficiary_name" class="form-control" maxlength="255" autocomplete="off" required></div>
                    <div class="form-group"><label class="step-input-label" for="alternate-relationship">Relationship to FR/FVE</label><input id="alternate-relationship" name="relationship" class="form-control" maxlength="255" required></div>
                    <div class="form-group"><label class="step-input-label" for="alternate-approval">Approval reference</label><input id="alternate-approval" name="approval_reference" class="form-control" maxlength="255" required></div>
                    <div class="row"><div class="form-group col-6"><label class="step-input-label" for="alternate-approval-status">Approval</label><select id="alternate-approval-status" name="approval_status" class="form-control">@foreach(\App\Models\EclipLivelihoodBeneficiaryAssistance::APPROVAL_STATUSES as $status)<option value="{{ $status }}">{{ str($status)->title() }}</option>@endforeach</select></div><div class="form-group col-6"><label class="step-input-label" for="alternate-amount">Amount</label><input id="alternate-amount" name="assistance_amount" class="form-control" type="number" min="0" step="0.01" required></div></div>
                    <div class="row"><div class="form-group col-6"><label class="step-input-label" for="alternate-release-status">Release status</label><select id="alternate-release-status" name="release_status" class="form-control">@foreach(\App\Models\EclipLivelihoodBeneficiaryAssistance::RELEASE_STATUSES as $status)<option value="{{ $status }}">{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select></div><div class="form-group col-6"><label class="step-input-label" for="alternate-release-date">Release date</label><input id="alternate-release-date" name="release_date" class="form-control" type="date" max="{{ now()->toDateString() }}"></div></div>
                    <div class="form-group"><label class="step-input-label" for="alternate-support">Supporting document reference</label><input id="alternate-support" name="supporting_reference" class="form-control" maxlength="255" required></div>
                    <div class="form-group"><label class="step-input-label" for="alternate-remarks">Remarks</label><textarea id="alternate-remarks" name="remarks" class="form-control" rows="2" maxlength="5000"></textarea></div>
                    <button class="btn btn-sm btn-outline-primary btn-block">Save identified beneficiary</button>
                </form>
            </details>
        @endif
        <div class="sidebar-document-list mt-3">@forelse($case->livelihoodBeneficiaryAssistances as $assistance)<details class="sidebar-document-item"><summary class="sidebar-document-heading"><strong>{{ $assistance->beneficiary_name }}</strong><span class="document-status status-pending">{{ str($assistance->release_status)->replace('_',' ')->title() }}</span></summary><small class="text-muted d-block mt-1">{{ $assistance->relationship }} · Approval {{ str($assistance->approval_status)->title() }} · Ref {{ $assistance->approval_reference }}</small><p class="history-remarks mt-2 mb-0">{{ $assistance->implementation_reason }}</p></details>@empty<div class="no-document"><i class="mdi mdi-account-off-outline"></i>No alternate beneficiary recorded. Mark Step 12 not applicable when direct implementation applies.</div>@endforelse</div>
    </div>
</section>
