<?php

/*
| SHIELD role metadata: human label for each role plus its sidebar navigation.
| The sidebar renders only items whose route already exists (Route::has).
| icon    = Heroicon name (Tailwind pages).
| skyicon = SkyDash icon class (simple-line-icons / themify) for the ported theme.
*/

return [
    'development_seed_password' => env('SHIELD_DEV_PASSWORD'),
    'eclip_delay_days' => max(1, (int) env('ECLIP_DELAY_DAYS', 7)),
    'eclip_basic_service_types' => [
        'health' => 'Health and Medical',
        'education' => 'Education and Skills Training',
        'housing' => 'Housing and Shelter',
        'legal' => 'Legal Assistance',
        'psychosocial' => 'Psychosocial Support',
        'livelihood_preparation' => 'Livelihood Preparation',
        'government_registration' => 'Government Registration',
        'employment' => 'Employment Assistance',
    ],
    'eclip_analytics_roles' => [
        'super_admin', 'admin', 'lswdo', 'dilg_provincial_focal', 'dilg_regional',
        'nboo_eclip_pmo', 'dilg_fms', 'local_eclip_committee',
        'eclip_assessor', 'dilg_reviewer', 'eclip_funding_officer',
    ],
    'eclip_financial_analytics_roles' => [
        'super_admin', 'admin', 'dilg_provincial_focal', 'dilg_regional',
        'nboo_eclip_pmo', 'dilg_fms', 'local_eclip_committee',
        'dilg_reviewer', 'eclip_funding_officer',
    ],

    'roles' => [
        'super_admin' => [
            'label' => 'Super Admin',
            'nav' => [
                ['label' => 'Dashboard',          'route' => 'super_admin.dashboard',      'icon' => 'squares-2x2',              'skyicon' => 'icon-grid'],
                ['label' => 'User Management',     'route' => 'super_admin.users.index',    'icon' => 'users',                    'skyicon' => 'ti-user'],
                ['label' => 'Government Agencies', 'route' => 'super_admin.agencies.index', 'icon' => 'building-office-2',        'skyicon' => 'icon-briefcase'],
                ['label' => 'System Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
                ['label' => 'General Audit Logs', 'route' => 'super_admin.audit-logs.index', 'icon' => 'clipboard-document-list', 'skyicon' => 'icon-list'],
            ],
        ],
        'admin' => [
            'label' => 'Katuparan Center',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid'],
                ['label' => 'SHIELD Monitoring', 'route' => 'admin.clusters.index', 'icon' => 'squares-plus', 'skyicon' => 'ti-layout'],
                ['label' => 'Cluster Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
                ['label' => 'Cluster Monitoring', 'route' => 'admin.rcsp.index', 'icon' => 'document-check', 'skyicon' => 'icon-drawer'],
                ['label' => 'Contribution Monitoring', 'route' => 'admin.implan.index', 'icon' => 'clipboard-document-list', 'skyicon' => 'icon-doc'],
            ],
        ],
        'lgu' => [
            'label' => 'DILG — LGU',
            'nav' => [
                ['label' => 'Dashboard',         'route' => 'lgu.dashboard',        'icon' => 'squares-2x2',             'skyicon' => 'icon-grid'],
                ['label' => 'RCSP Barangays',    'route' => 'lgu.rcsp.index',       'icon' => 'map',                     'skyicon' => 'icon-map', 'active' => ['lgu.monitoring.*']],
                ['label' => 'Evaluation Status', 'route' => 'lgu.evaluation.index', 'icon' => 'chart-bar',               'skyicon' => 'icon-graph'],
                ['label' => 'IMPLAN',            'route' => 'lgu.implan.index',     'icon' => 'clipboard-document-list', 'skyicon' => 'icon-doc'],
            ],
        ],
        'gov_agency' => [
            'label' => 'Government Agency',
            'nav' => [
                ['label' => 'Dashboard',   'route' => 'gov_agency.dashboard',   'icon' => 'squares-2x2',             'skyicon' => 'icon-grid'],
                ['label' => 'IMPLAN List', 'route' => 'gov_agency.implan.index', 'icon' => 'clipboard-document-list', 'skyicon' => 'icon-doc'],
                ['label' => 'E-CLIP Referrals', 'route' => 'gov_agency.eclip.basic-services.index', 'icon' => 'heart', 'skyicon' => 'icon-heart'],
            ],
        ],
        'mblrc' => [
            'label' => 'MBLRC',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'mblrc.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'FR/FVE Registry', 'route' => 'mblrc.fr.index', 'icon' => 'user-group', 'skyicon' => 'icon-people', 'section' => 'Reintegration'],
                ['label' => 'Integration Monitoring', 'route' => 'mblrc.enrollments.index', 'icon' => 'clipboard-document-check', 'skyicon' => 'icon-note', 'section' => 'Reintegration'],
                ['label' => 'E-CLIP Cases', 'route' => 'mblrc.eclip.index', 'icon' => 'folder', 'skyicon' => 'icon-folder', 'section' => 'Case Management'],
            ],
        ],
        'lswdo' => [
            'label' => 'LSWDO',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'lswdo.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'MBLRC Referrals', 'route' => 'lswdo.referrals.index', 'icon' => 'inbox-arrow-down', 'skyicon' => 'icon-envelope', 'section' => 'Case Intake'],
                ['label' => 'E-CLIP Cases', 'route' => 'lswdo.eclip.index', 'icon' => 'folder', 'skyicon' => 'icon-folder', 'section' => 'Case Management'],
            ],
        ],
        'japic' => [
            'label' => 'JAPIC',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'japic.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'Authentication Queue', 'route' => 'japic.authentication.index', 'icon' => 'shield-check', 'skyicon' => 'icon-shield', 'section' => 'Authentication'],
                ['label' => 'E-CLIP Documents', 'route' => 'japic.eclip.index', 'icon' => 'document-check', 'skyicon' => 'icon-docs', 'section' => 'Document Review'],
            ],
        ],
        'dilg_provincial_focal' => [
            'label' => 'DILG Provincial/HUC/ICC E-CLIP Focal Person',
            'nav' => [
                ['label' => 'Provincial Validation', 'route' => 'dilg_reviewer.cases.index', 'icon' => 'document-check', 'skyicon' => 'icon-check'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        'dilg_regional' => [
            'label' => 'DILG Regional Office',
            'nav' => [
                ['label' => 'Regional Review', 'route' => 'dilg_reviewer.cases.index', 'icon' => 'document-check', 'skyicon' => 'icon-check'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        'nboo_eclip_pmo' => [
            'label' => 'NBOO / ECLIP-PMO',
            'nav' => [
                ['label' => 'National Processing', 'route' => 'dilg_reviewer.cases.index', 'icon' => 'document-check', 'skyicon' => 'icon-check'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        'dilg_fms' => [
            'label' => 'DILG FMS',
            'nav' => [
                ['label' => 'Fund Allocation', 'route' => 'eclip_funding.cases.index', 'icon' => 'banknotes', 'skyicon' => 'icon-wallet'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        'local_eclip_committee' => [
            'label' => 'Local E-CLIP Committee',
            'nav' => [
                ['label' => 'Surfaced FR/FVE', 'route' => 'local_eclip.surfaced.index', 'icon' => 'account-group', 'skyicon' => 'icon-people', 'section' => 'Case Monitoring'],
                ['label' => 'Assistance Release', 'route' => 'local_eclip.cases.index', 'icon' => 'hand-raised', 'skyicon' => 'icon-present', 'section' => 'Case Management'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph', 'section' => 'Monitoring'],
            ],
        ],
        '39th_ib' => [
            'label' => '39th IB',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'ib39.dashboard',   'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'FR Profiles', 'route' => 'ib39.fr-profiles.index', 'icon' => 'users', 'skyicon' => 'icon-people', 'section' => 'Reintegration'],
                ['label' => 'Add Area',  'route' => 'ib39.areas.index', 'icon' => 'map-pin',     'skyicon' => 'icon-map', 'section' => 'Operational Areas'],
                ['label' => 'Map',       'route' => 'ib39.map',         'icon' => 'map',         'skyicon' => 'icon-location-pin', 'section' => 'Operational Areas'],
            ],
        ],
        'afp' => [
            'label' => 'AFP',
            'nav' => [
                ['label' => 'Dashboard',      'route' => 'afp.dashboard',   'icon' => 'squares-2x2', 'skyicon' => 'icon-grid'],
                ['label' => 'RCSP Barangays', 'route' => 'afp.rcsp.index',  'icon' => 'map',         'skyicon' => 'icon-map'],
            ],
        ],
        'pnp' => [
            'label' => 'PNP',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'pnp.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'FEA Queue', 'route' => 'pnp.eclip-fea.index', 'icon' => 'document-check', 'skyicon' => 'icon-docs', 'section' => 'Case Processing'],
            ],
        ],
    ],
];
