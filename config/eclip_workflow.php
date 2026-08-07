<?php

return [
    'phases' => [
        1 => [
            'name' => 'Pre-Surfacing',
            'description' => 'Initial expression of intent and E-CLIP orientation.',
        ],
        2 => [
            'name' => 'Actual Surfacing and Claims Processing',
            'description' => 'Eligibility validation, authentication, enrolment, assessment, and claims processing.',
        ],
        3 => [
            'name' => 'Settlement of Claims',
            'description' => 'Issuance, awarding, and encashment of approved assistance.',
        ],
        4 => [
            'name' => 'Liquidation of Funds',
            'description' => 'Submission and audit of liquidation and disbursement records.',
        ],
        5 => [
            'name' => 'Reintegration',
            'description' => 'Planning, implementation, discharge, and continuing reintegration support.',
        ],
    ],
    'steps' => [
        ['code' => '1', 'phase' => 1, 'title' => 'FR/FVE Signifies Intention to Surface', 'roles' => [], 'automatic' => true],
        ['code' => '2', 'phase' => 1, 'title' => 'Orientation on the E-CLIP Program', 'roles' => [], 'automatic' => true, 'depends_on' => ['1']],
        ['code' => '3A', 'phase' => 2, 'title' => 'Validation of Previous Assistance', 'roles' => ['lswdo'], 'depends_on' => ['2']],
        ['code' => '3B', 'phase' => 2, 'title' => 'Endorsement for Processing', 'roles' => ['lswdo'], 'depends_on' => ['3A']],
        ['code' => '4A', 'phase' => 2, 'title' => 'JAPIC Authentication Procedures', 'roles' => ['japic'], 'depends_on' => ['3B'], 'working_days' => 10],
        ['code' => '4B', 'phase' => 2, 'title' => 'Firearms Processing', 'roles' => ['pnp'], 'depends_on' => ['3B'], 'allow_na' => true],
        ['code' => '4C', 'phase' => 2, 'title' => 'Submission of JAPIC Certification', 'roles' => ['japic'], 'depends_on' => ['4A'], 'documents' => ['JAPIC Certification']],
        ['code' => '4D', 'phase' => 2, 'title' => 'Accomplishment of the E-CLIP Enrolment Form', 'roles' => ['lswdo'], 'depends_on' => ['4C'], 'documents' => ['E-CLIP Enrolment Form (Form 2)']],
        ['code' => '4E', 'phase' => 2, 'title' => 'Provision of Social Protection Services', 'roles' => ['lswdo'], 'depends_on' => ['4D']],
        ['code' => '5A', 'phase' => 2, 'title' => 'Initial Interview, Admission Orientation, and Profiling', 'roles' => ['lswdo'], 'depends_on' => ['4E'], 'documents' => ['Initial Interview Form (Form 1)', 'Profiling Interview Form (Form 3)']],
        ['code' => '5B', 'phase' => 2, 'title' => 'Provision of Halfway House Support Services', 'roles' => ['local_eclip_committee'], 'depends_on' => ['5A']],
        ['code' => '6A', 'phase' => 2, 'title' => 'Encoding of Beneficiary Profile in the ECLIP Information System', 'roles' => ['lswdo'], 'depends_on' => ['5B']],
        ['code' => '6B', 'phase' => 2, 'title' => 'Processing of Assistance and Additional Requirements', 'roles' => ['lswdo'], 'depends_on' => ['6A']],
        ['code' => '6C', 'phase' => 2, 'title' => 'Review and Generation of Endorsement Letter', 'roles' => ['lswdo'], 'depends_on' => ['6B'], 'documents' => ['Endorsement Letter (Form 7)']],
        ['code' => '6D', 'phase' => 2, 'title' => 'Verification by DILG Provincial/HUC/ICC Office', 'roles' => ['dilg_provincial_focal'], 'depends_on' => ['6C'], 'working_days' => 2],
        ['code' => '6E', 'phase' => 2, 'title' => 'Evaluation by DILG Regional Office', 'roles' => ['dilg_regional'], 'depends_on' => ['6D'], 'working_days' => 1, 'documents' => ['Regional Form 8', 'Form 9']],
        ['code' => '6F', 'phase' => 2, 'title' => 'Evaluation by DILG NBOO', 'roles' => ['nboo_eclip_pmo'], 'depends_on' => ['6E'], 'working_days' => 2, 'documents' => ['Form 9', 'Form 10']],
        ['code' => '6G', 'phase' => 2, 'title' => 'Processing of SR and NTA', 'roles' => ['dilg_fms'], 'depends_on' => ['6F'], 'working_days' => 5, 'documents' => ['Sub-Allotment Release Order', 'Notice of Transfer Allocation']],
        ['code' => '6H', 'phase' => 2, 'title' => 'Recording of Fund Transfer Details', 'roles' => ['dilg_fms'], 'depends_on' => ['6G'], 'documents' => ['Sub-Allotment Release Order']],
        ['code' => '6I', 'phase' => 2, 'title' => 'Transfer of Funds to DILG Provincial/HUC/ICC Office', 'roles' => ['dilg_regional'], 'depends_on' => ['6H'], 'working_days' => 2, 'documents' => ['Notice of Transfer Allocation']],
        ['code' => '7A', 'phase' => 3, 'title' => 'Processing and Issuance of Check', 'roles' => ['dilg_provincial_focal'], 'depends_on' => ['6I'], 'working_days' => 5],
        ['code' => '7B', 'phase' => 3, 'title' => 'Awarding of Check and Assistance in Encashment', 'roles' => ['lswdo', 'local_eclip_committee'], 'depends_on' => ['7A']],
        ['code' => '8A', 'phase' => 4, 'title' => 'Submission of Liquidation Documents', 'roles' => ['dilg_provincial_focal'], 'depends_on' => ['7B'], 'documents' => ['Form 11', 'Applicable liquidation supporting documents']],
        ['code' => '9', 'phase' => 4, 'title' => 'Submission of Disbursement Reports for Auditing', 'roles' => ['dilg_regional'], 'depends_on' => ['8A'], 'working_days' => 5, 'documents' => ['Finalized Form 11']],
        ['code' => '10', 'phase' => 5, 'title' => 'Preparation of the Reintegration Plan', 'roles' => ['lswdo'], 'depends_on' => ['9'], 'documents' => ['FRRP (Form 4) or FVERP (Form 5)', 'Business Plan (Form 6)']],
        ['code' => '11', 'phase' => 5, 'title' => 'Implementation of the Reintegration Plan', 'roles' => ['lswdo', 'gov_agency'], 'depends_on' => ['10']],
        ['code' => '12', 'phase' => 5, 'title' => 'Livelihood Assistance to Identified Beneficiary', 'roles' => ['lswdo', 'local_eclip_committee'], 'depends_on' => ['10'], 'allow_na' => true],
        ['code' => '13', 'phase' => 5, 'title' => 'Discharge from the Halfway House', 'roles' => ['lswdo', 'local_eclip_committee'], 'depends_on' => ['11', '12']],
        ['code' => '14', 'phase' => 5, 'title' => 'Continued Reintegration Assistance', 'roles' => ['lswdo', 'gov_agency'], 'depends_on' => ['13']],
    ],
];
