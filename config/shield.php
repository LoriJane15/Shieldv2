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
                ['label' => 'E-CLIP Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        'admin' => [
            'label' => 'Katuparan Center',
            'nav' => [
                ['label' => 'Dashboard',  'route' => 'admin.dashboard',       'icon' => 'squares-2x2',              'skyicon' => 'icon-grid'],
                ['label' => 'Clusters',   'route' => 'admin.clusters.index',  'icon' => 'squares-plus',             'skyicon' => 'ti-layout'],
                ['label' => 'Agencies',   'route' => 'admin.agencies.index',  'icon' => 'building-office-2',        'skyicon' => 'icon-briefcase'],
                ['label' => 'Locations',  'route' => 'admin.locations.index', 'icon' => 'map-pin',                  'skyicon' => 'icon-location-pin'],
                ['label' => 'RCSP Forms', 'route' => 'admin.rcsp.index',      'icon' => 'document-check',           'skyicon' => 'icon-drawer'],
                ['label' => 'IMPLAN',     'route' => 'admin.implan.index',    'icon' => 'clipboard-document-list',  'skyicon' => 'icon-doc'],
                ['label' => 'E-CLIP Documents', 'route' => 'admin.eclip.requirements.index', 'icon' => 'document', 'skyicon' => 'icon-docs'],
                ['label' => 'E-CLIP Assistance', 'route' => 'admin.eclip.assistance-categories.index', 'icon' => 'banknotes', 'skyicon' => 'icon-wallet'],
                ['label' => 'E-CLIP Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
                ['label' => 'Users',      'route' => 'admin.users.index',     'icon' => 'user-group',               'skyicon' => 'icon-user'],
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
                ['label' => 'Dashboard',     'route' => 'mblrc.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid'],
                ['label' => 'Former Rebels', 'route' => 'mblrc.fr.index',  'icon' => 'user-group',  'skyicon' => 'icon-people'],
                ['label' => 'E-CLIP Cases', 'route' => 'mblrc.eclip.index', 'icon' => 'folder', 'skyicon' => 'icon-folder'],
            ],
        ],
        'lswdo' => [
            'label' => 'LSWDO',
            'nav' => [
                ['label' => 'E-CLIP Eligibility', 'route' => 'lswdo.eclip.index', 'icon' => 'document-check', 'skyicon' => 'icon-check'],
                ['label' => 'Assistance Assessment', 'route' => 'eclip_assessor.cases.index', 'icon' => 'clipboard-document-check', 'skyicon' => 'icon-note'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        'japic' => [
            'label' => 'JAPIC',
            'nav' => [
                ['label' => 'E-CLIP Documents', 'route' => 'japic.eclip.index', 'icon' => 'document-check', 'skyicon' => 'icon-docs'],
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
                ['label' => 'Assistance Release', 'route' => 'local_eclip.cases.index', 'icon' => 'hand-raised', 'skyicon' => 'icon-present'],
                ['label' => 'Analytics', 'route' => 'eclip.analytics.index', 'icon' => 'chart-bar', 'skyicon' => 'icon-graph'],
            ],
        ],
        '39th_ib' => [
            'label' => '39th IB',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'ib39.dashboard',   'icon' => 'squares-2x2', 'skyicon' => 'icon-grid'],
                ['label' => 'Add Area',  'route' => 'ib39.areas.index', 'icon' => 'map-pin',     'skyicon' => 'icon-map'],
                ['label' => 'Map',       'route' => 'ib39.map',         'icon' => 'map',         'skyicon' => 'icon-location-pin'],
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
                ['label' => 'Dashboard', 'route' => 'pnp.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid'],
            ],
        ],
    ],
];
