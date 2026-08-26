<?php

return [
    'phases' => [
        1 => [
            'name' => 'Pre-Surfacing',
            'description' => 'Initial expression of intent and E-CLIP orientation.',
        ],
        2 => [
            'name' => 'Actual Surfacing',
            'description' => 'Eligibility validation, authentication, enrolment, protection services, and initial interviews.',
        ],
        3 => [
            'name' => 'Settlement and Liquidation',
            'description' => 'Claims review, funding, release, liquidation, and regional disbursement reporting.',
        ],
        4 => [
            'name' => 'Reintegration',
            'description' => 'Planning, implementation, discharge, and continuing reintegration support.',
        ],
    ],
    'steps' => [
        ['code' => '1', 'phase' => 1, 'title' => 'FR/FVE Signifies Intention to Surface', 'roles' => ['mblrc'], 'evidence_key' => 'intention_to_surface', 'fields' => [
            ['key' => 'intention_date', 'label' => 'Date of intention', 'type' => 'date'], ['key' => 'receiving_unit', 'label' => 'Receiving unit'], ['key' => 'responsible_personnel', 'label' => 'Responsible personnel'], ['key' => 'graduate_list_reference', 'label' => 'Three-month graduate list reference'], ['key' => 'submission_date', 'label' => 'Submission date', 'type' => 'date'],
        ]],
        ['code' => '2', 'phase' => 1, 'title' => 'LSWDO Informs the Local E-CLIP Committee', 'roles' => ['lswdo'], 'depends_on' => ['1'], 'fields' => [
            ['key' => 'receiving_committee', 'label' => 'Receiving committee', 'required_on_complete' => true], ['key' => 'submission_date', 'label' => 'Submission date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'confirmation_timestamp', 'label' => 'Confirmation date and time', 'type' => 'datetime-local', 'required_on_complete' => true], ['key' => 'notification_reference', 'label' => 'Notification reference', 'required_on_complete' => true],
        ]],
        ['code' => '3A', 'phase' => 2, 'title' => 'Record Program Eligibility', 'roles' => ['lswdo'], 'depends_on' => ['2'], 'fields' => [
            ['key' => 'non_eligibility_reason', 'label' => 'Reason for non-eligibility or clarification', 'type' => 'textarea'],
            ['key' => 'referral_status', 'label' => 'Referral status'],
            ['key' => 'referred_program', 'label' => 'Program referred to, if applicable'],
        ]],
        ['code' => '3B', 'phase' => 2, 'title' => 'Endorse Eligible FR/FVE for Processing', 'roles' => ['lswdo'], 'depends_on' => ['3A']],
        ['code' => '4A', 'phase' => 2, 'title' => 'JAPIC Authentication and Certification', 'roles' => ['japic'], 'depends_on' => ['3B'], 'working_days' => 10, 'documents' => ['JAPIC Certification'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'authentication_date', 'label' => 'Authentication date', 'type' => 'date'], ['key' => 'certification_reference', 'label' => 'Certification reference'],
        ]],
        ['code' => '4B', 'phase' => 2, 'title' => 'FEA Processing', 'roles' => ['pnp', 'afp'], 'depends_on' => ['3B'], 'allow_na' => true, 'documents' => ['Property Turn-In-Slip (PTIS)', 'Technical Inspection Report (TIR)', 'Cost Valuation of Inventory Firearms (CVIF)'], 'fields' => [
            ['key' => 'fea_status', 'label' => 'FEA processing status'],
            ['key' => 'fea_reference', 'label' => 'FEA processing reference'],
            ['key' => 'processing_stage', 'label' => 'Processing stage'], ['key' => 'responsible_office', 'label' => 'Responsible office'], ['key' => 'date_updated', 'label' => 'Date updated', 'type' => 'date'],
        ]],
        ['code' => '4C', 'phase' => 2, 'title' => 'Accomplish E-CLIP Enrollment Form', 'roles' => ['lswdo'], 'depends_on' => ['4A'], 'documents' => ['E-CLIP Enrollment Form (Form 2)'], 'enforce_documents' => true],
        ['code' => '4D', 'phase' => 2, 'title' => 'Provide Social Protection Services', 'roles' => ['lswdo'], 'depends_on' => ['4C'], 'fields' => [
            ['key' => 'service_name', 'label' => 'Social protection service provided'],
            ['key' => 'service_date', 'label' => 'Date provided', 'type' => 'date'],
            ['key' => 'service_provider', 'label' => 'Provider or responsible office'],
        ]],
        ['code' => '5A', 'phase' => 2, 'title' => 'Conduct Initial and Profiling Interviews', 'roles' => ['lswdo'], 'depends_on' => ['4D'], 'documents' => ['Initial Interview Form (Form 1)', 'Profiling Interview Form (Form 3)'], 'enforce_documents' => true],
        ['code' => '6A', 'phase' => 3, 'title' => 'Encode and Upload Required Files to E-CLIP IS', 'roles' => ['lswdo'], 'depends_on' => ['5A'], 'fields' => [
            ['key' => 'initial_interview_uploaded', 'label' => 'Initial Interview Form uploaded to E-CLIP IS', 'type' => 'checkbox'],
            ['key' => 'enrollment_form_uploaded', 'label' => 'Enrollment Form uploaded to E-CLIP IS', 'type' => 'checkbox'],
            ['key' => 'id_uploaded', 'label' => 'ID uploaded to E-CLIP IS', 'type' => 'checkbox'],
            ['key' => 'japic_certification_uploaded', 'label' => 'JAPIC certification uploaded to E-CLIP IS', 'type' => 'checkbox'],
            ['key' => 'frrp_fverp_uploaded', 'label' => 'FRRP/FVERP uploaded, when applicable', 'type' => 'checkbox'],
            ['key' => 'business_plan_uploaded', 'label' => 'Business Plan / Mungkahing Proyekto uploaded, when applicable', 'type' => 'checkbox'],
            ['key' => 'ptis_uploaded', 'label' => 'PTIS uploaded, when applicable', 'type' => 'checkbox'],
            ['key' => 'tir_uploaded', 'label' => 'TIR uploaded, when applicable', 'type' => 'checkbox'],
            ['key' => 'cvif_uploaded', 'label' => 'CVIF uploaded, when applicable', 'type' => 'checkbox'],
        ]],
        ['code' => '6B', 'phase' => 3, 'title' => 'Generate and Submit Endorsement Letter', 'roles' => ['lswdo'], 'depends_on' => ['6A'], 'documents' => ['Endorsement Letter (Form 7)'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'endorsement_reference', 'label' => 'Endorsement letter reference', 'required_on_complete' => true],
            ['key' => 'generation_date', 'label' => 'Generation date', 'type' => 'date', 'required_on_complete' => true],
            ['key' => 'submitted_to_eclip_is_at', 'label' => 'Submitted to E-CLIP IS on', 'type' => 'date', 'required_on_complete' => true],
        ]],
        ['code' => '6C', 'phase' => 3, 'title' => 'Monitor Claim Processing', 'roles' => ['lswdo'], 'depends_on' => ['6B'], 'allow_na' => true, 'fields' => [
            ['key' => 'processing_office', 'label' => 'Current processing office'], ['key' => 'submitted_at', 'label' => 'Date submitted', 'type' => 'date'], ['key' => 'returned_at', 'label' => 'Date returned', 'type' => 'date'], ['key' => 'return_reason', 'label' => 'Return reason', 'type' => 'textarea'], ['key' => 'resubmitted_at', 'label' => 'Resubmission date', 'type' => 'date'], ['key' => 'completion_date', 'label' => 'Completion date', 'type' => 'date'],
        ]],
        ['code' => '6D', 'phase' => 3, 'title' => 'DILG P/HUC/ICC Review and Form 8 Endorsement', 'roles' => ['dilg_provincial_focal'], 'depends_on' => ['6C'], 'working_days' => 2, 'fields' => [
            ['key' => 'form_8_reference', 'label' => 'Signed Form 8 reference', 'required_on_complete' => true], ['key' => 'endorsement_date', 'label' => 'Endorsement date', 'type' => 'date', 'required_on_complete' => true],
        ]],
        ['code' => '6E', 'phase' => 3, 'title' => 'DILG Regional Review and NBOO Endorsement', 'roles' => ['dilg_regional'], 'depends_on' => ['6D'], 'working_days' => 1, 'documents' => ['Form 9'], 'fields' => [
            ['key' => 'form_9_reference', 'label' => 'Form 9 reference', 'required_on_complete' => true], ['key' => 'endorsement_date', 'label' => 'Endorsement date', 'type' => 'date', 'required_on_complete' => true],
        ]],
        ['code' => '6F', 'phase' => 3, 'title' => 'NBOO Review and DILG FMS Endorsement', 'roles' => ['nboo_eclip_pmo'], 'depends_on' => ['6E'], 'working_days' => 2, 'documents' => ['Form 10'], 'fields' => [
            ['key' => 'form_10_reference', 'label' => 'Form 10 reference', 'required_on_complete' => true], ['key' => 'endorsement_date', 'label' => 'Endorsement date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'eclip_is_reference', 'label' => 'E-CLIP IS reference'],
        ]],
        ['code' => '6G', 'phase' => 3, 'title' => 'DILG FMS Issues SR and NTA', 'roles' => ['dilg_fms'], 'depends_on' => ['6F'], 'working_days' => 5, 'documents' => ['Sub-Allotment Release Order', 'Notice of Transfer Allocation'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'sr_number', 'label' => 'SR number', 'required_on_complete' => true], ['key' => 'sr_date', 'label' => 'SR date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'nta_number', 'label' => 'NTA number', 'required_on_complete' => true], ['key' => 'nta_date', 'label' => 'NTA date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'approved_amount', 'label' => 'Approved amount', 'type' => 'number', 'required_on_complete' => true], ['key' => 'receiving_regional_office', 'label' => 'Receiving DILG Regional Office', 'required_on_complete' => true],
        ]],
        ['code' => '6H', 'phase' => 3, 'title' => 'Record Fund Transfer in E-CLIP IS', 'roles' => ['dilg_fms'], 'depends_on' => ['6G'], 'fields' => [
            ['key' => 'eclip_is_recorded', 'label' => 'SR and transferred amount recorded in E-CLIP IS', 'type' => 'checkbox'], ['key' => 'eclip_is_updated_at', 'label' => 'E-CLIP IS update date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'sr_number', 'label' => 'Approved SR reference', 'required_on_complete' => true], ['key' => 'amount_transferred', 'label' => 'Amount transferred', 'type' => 'number', 'required_on_complete' => true], ['key' => 'responsible_office', 'label' => 'Responsible office', 'required_on_complete' => true],
        ]],
        ['code' => '6I', 'phase' => 3, 'title' => 'DILG Regional Transfers Funds to P/HUC/ICC', 'roles' => ['dilg_regional'], 'depends_on' => ['6H'], 'working_days' => 2, 'fields' => [
            ['key' => 'nta_received_date', 'label' => 'NTA received date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'fund_transfer_date', 'label' => 'Fund-transfer date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'amount_transferred', 'label' => 'Amount transferred', 'type' => 'number', 'required_on_complete' => true], ['key' => 'recipient_office', 'label' => 'Recipient DILG P/HUC/ICC office', 'required_on_complete' => true], ['key' => 'proof_reference', 'label' => 'Fund-transfer proof or reference', 'required_on_complete' => true],
        ]],
        ['code' => '7A', 'phase' => 3, 'title' => 'Process and Issue Check', 'roles' => ['dilg_provincial_focal'], 'depends_on' => ['6I'], 'working_days' => 5, 'fields' => [
            ['key' => 'check_status', 'label' => 'Check processing status', 'type' => 'select', 'options' => ['check_processing' => 'Check Processing', 'check_issued' => 'Check Issued', 'scheduled_for_turnover' => 'Scheduled for Turnover', 'returned' => 'Returned', 'completed' => 'Completed'], 'required_on_complete' => true], ['key' => 'assistance_type', 'label' => 'Assistance type', 'required_on_complete' => true], ['key' => 'amount', 'label' => 'Amount', 'type' => 'number', 'required_on_complete' => true], ['key' => 'payee', 'label' => 'Payee', 'required_on_complete' => true], ['key' => 'check_reference', 'label' => 'Masked check or reference number', 'required_on_complete' => true], ['key' => 'issue_date', 'label' => 'Issue date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'turnover_schedule', 'label' => 'Turnover schedule', 'type' => 'date', 'required_on_complete' => true],
        ]],
        ['code' => '7B', 'phase' => 3, 'title' => 'Award Check and Record Assistance Release', 'roles' => ['lswdo', 'local_eclip_committee'], 'depends_on' => ['7A'], 'documents' => ['Signed Acknowledgment', 'Disbursement Voucher', 'Authorized Photo Evidence'], 'fields' => [
            ['key' => 'release_date', 'label' => 'Actual turnover or release date', 'type' => 'date'], ['key' => 'recipient', 'label' => 'Recipient'], ['key' => 'assistance_type', 'label' => 'Assistance type'], ['key' => 'amount_released', 'label' => 'Amount released', 'type' => 'number'],
        ]],
        ['code' => '8A', 'phase' => 3, 'title' => 'Submit Liquidation Requirements through E-CLIP IS', 'roles' => ['dilg_provincial_focal'], 'depends_on' => ['7B'], 'documents' => ['Form 11', 'Applicable liquidation supporting documents'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'assistance_category', 'label' => 'Assistance category', 'required_on_complete' => true], ['key' => 'liquidation_reference', 'label' => 'Form 11 or liquidation reference', 'required_on_complete' => true], ['key' => 'submission_date', 'label' => 'Submission date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'return_date', 'label' => 'Return date', 'type' => 'date'], ['key' => 'return_reason', 'label' => 'Return reason', 'type' => 'textarea'], ['key' => 'resubmission_date', 'label' => 'Resubmission date', 'type' => 'date'], ['key' => 'acceptance_date', 'label' => 'Acceptance date', 'type' => 'date', 'required_on_complete' => true],
        ]],
        ['code' => '9', 'phase' => 3, 'title' => 'Submit and Maintain Regional Disbursement Report', 'roles' => ['dilg_regional'], 'depends_on' => ['8A'], 'working_days' => 5, 'documents' => ['Finalized Form 11'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'reporting_month', 'label' => 'Reporting month', 'type' => 'month', 'required_on_complete' => true], ['key' => 'form_11_reference', 'label' => 'Form 11 reference', 'required_on_complete' => true], ['key' => 'submission_date', 'label' => 'Submission date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'recipients', 'label' => 'Recipients', 'type' => 'textarea', 'required_on_complete' => true], ['key' => 'correction_history', 'label' => 'Return / correction history', 'type' => 'textarea'],
        ]],
        ['code' => '10', 'phase' => 4, 'title' => 'Prepare FRRP/FVERP and Business Plan', 'roles' => ['lswdo'], 'depends_on' => ['9'], 'documents' => ['FRRP (Form 4) or FVERP (Form 5)', 'Business Plan (Form 6)'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'identified_need', 'label' => 'Identified need', 'type' => 'textarea'], ['key' => 'proposed_assistance', 'label' => 'Proposed assistance'], ['key' => 'responsible_agency', 'label' => 'Responsible agency'], ['key' => 'lgu_counterpart', 'label' => 'LGU counterpart'], ['key' => 'form_of_assistance', 'label' => 'Form of assistance'], ['key' => 'amount', 'label' => 'Amount', 'type' => 'number'], ['key' => 'target_date', 'label' => 'Target date', 'type' => 'date'], ['key' => 'partner_agencies', 'label' => 'Partner agencies consulted', 'type' => 'textarea'], ['key' => 'agency_commitments', 'label' => 'Agency commitments', 'type' => 'textarea'],
        ]],
        ['code' => '11', 'phase' => 4, 'title' => 'Implement Approved Reintegration Assistance', 'roles' => ['lswdo', 'gov_agency'], 'depends_on' => ['10'], 'fields' => [
            ['key' => 'provider', 'label' => 'Responsible provider'], ['key' => 'referral_date', 'label' => 'Referral date', 'type' => 'date'], ['key' => 'intervention_status', 'label' => 'Intervention status'], ['key' => 'target_date', 'label' => 'Target date', 'type' => 'date'], ['key' => 'amount_value', 'label' => 'Amount / value', 'type' => 'number'], ['key' => 'outcome', 'label' => 'Outcome', 'type' => 'textarea'], ['key' => 'next_required_action', 'label' => 'Delay / return reason and next action', 'type' => 'textarea'],
        ]],
        ['code' => '12', 'phase' => 4, 'title' => 'Provide Livelihood Assistance through an Identified Beneficiary', 'roles' => ['lswdo', 'local_eclip_committee'], 'depends_on' => ['10'], 'allow_na' => true, 'documents' => ['Approval Evidence', 'Signed Acknowledgment'], 'enforce_documents' => true, 'fields' => [
            ['key' => 'implementation_reason', 'label' => 'Why the FR/FVE cannot directly implement the project', 'type' => 'textarea'], ['key' => 'beneficiary', 'label' => 'Identified beneficiary'], ['key' => 'relationship', 'label' => 'Relationship to FR/FVE'], ['key' => 'approval_reference', 'label' => 'Approval reference'], ['key' => 'assistance_amount', 'label' => 'Assistance amount', 'type' => 'number'], ['key' => 'release_status', 'label' => 'Release status'], ['key' => 'release_date', 'label' => 'Release date', 'type' => 'date'],
        ]],
        ['code' => '13', 'phase' => 4, 'title' => 'Record Healing, Reconciliation, and Community Transition', 'roles' => ['lswdo'], 'depends_on' => ['11', '12'], 'fields' => [
            ['key' => 'healing_status', 'label' => 'Healing activities', 'type' => 'select', 'options' => ['completed' => 'Completed', 'not_applicable' => 'Not Applicable', 'not_completed' => 'Not Completed'], 'required_on_complete' => true], ['key' => 'reconciliation_status', 'label' => 'Reconciliation activities', 'type' => 'select', 'options' => ['completed' => 'Completed', 'not_applicable' => 'Not Applicable', 'not_completed' => 'Not Completed'], 'required_on_complete' => true], ['key' => 'livelihood_status', 'label' => 'Livelihood assistance', 'type' => 'select', 'options' => ['completed' => 'Received', 'not_applicable' => 'Not Applicable', 'not_completed' => 'Not Received'], 'required_on_complete' => true], ['key' => 'community_transition_date', 'label' => 'Community-transition date', 'type' => 'date', 'required_on_complete' => true], ['key' => 'follow_up_schedule', 'label' => 'Follow-up schedule', 'required_on_complete' => true], ['key' => 'current_area', 'label' => 'Current area', 'required_on_complete' => true],
        ]],
        ['code' => '14', 'phase' => 4, 'title' => 'Provide and Close Remaining Reintegration Assistance', 'roles' => ['lswdo', 'gov_agency'], 'depends_on' => ['13'], 'fields' => [
            ['key' => 'provider', 'label' => 'Provider'], ['key' => 'target_date', 'label' => 'Target date', 'type' => 'date'], ['key' => 'completion_date', 'label' => 'Completion date', 'type' => 'date'], ['key' => 'amount_value', 'label' => 'Amount / value', 'type' => 'number'], ['key' => 'receiving_agency', 'label' => 'Receiving agency, when transferred'], ['key' => 'not_applicable_reason', 'label' => 'Not-applicable reason', 'type' => 'textarea'], ['key' => 'final_outcome', 'label' => 'Final outcome', 'type' => 'textarea'],
        ]],
    ],
];
