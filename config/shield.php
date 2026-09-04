<?php

/*
| SHIELD role metadata: human label for each role plus its sidebar navigation.
| The sidebar renders only items whose route already exists (Route::has).
| icon    = Heroicon name (Tailwind pages).
| skyicon = SkyDash icon class (simple-line-icons / themify) for the ported theme.
*/

return [
    'development_seed_password' => env('SHIELD_DEV_PASSWORD'),
    'roles' => [
        'super_admin' => [
            'label' => 'Super Admin',
            'nav' => [
                ['label' => 'Dashboard',          'route' => 'super_admin.dashboard',      'icon' => 'squares-2x2',              'skyicon' => 'icon-grid'],
                ['label' => 'User Management',     'route' => 'super_admin.users.index',    'icon' => 'users',                    'skyicon' => 'ti-user'],
                ['label' => 'Government Agencies', 'route' => 'super_admin.agencies.index', 'icon' => 'building-office-2',        'skyicon' => 'icon-briefcase'],
                ['label' => 'General Audit Logs', 'route' => 'super_admin.audit-logs.index', 'icon' => 'clipboard-document-list', 'skyicon' => 'icon-list'],
            ],
        ],
        'admin' => [
            'label' => 'Katuparan Center',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid'],
                ['label' => 'SHIELD Monitoring', 'route' => 'admin.clusters.index', 'icon' => 'squares-plus', 'skyicon' => 'ti-layout'],
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
            ],
        ],
        'mblrc' => [
            'label' => 'MBLRC',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'mblrc.dashboard', 'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'FR/FVE Registry', 'route' => 'mblrc.fr.index', 'icon' => 'user-group', 'skyicon' => 'icon-people', 'section' => 'Reintegration'],
            ],
        ],
        'lswdo' => [
            'label' => 'LSWDO',
            'nav' => [],
        ],
        'japic' => [
            'label' => 'JAPIC',
            'nav' => [],
        ],
        'dilg_provincial_focal' => [
            'label' => 'DILG Provincial/HUC/ICC E-CLIP Focal Person',
            'nav' => [
            ],
        ],
        'dilg_regional' => [
            'label' => 'DILG Regional Office',
            'nav' => [
            ],
        ],
        'nboo_eclip_pmo' => [
            'label' => 'NBOO / ECLIP-PMO',
            'nav' => [
            ],
        ],
        'dilg_fms' => [
            'label' => 'DILG FMS',
            'nav' => [
            ],
        ],
        'local_eclip_committee' => [
            'label' => 'Local E-CLIP Committee',
            'nav' => [
            ],
        ],
        '39th_ib' => [
            'label' => '39th IB',
            'nav' => [
                ['label' => 'Dashboard', 'route' => 'ib39.dashboard',   'icon' => 'squares-2x2', 'skyicon' => 'icon-grid', 'section' => 'Overview'],
                ['label' => 'FR Profiles', 'route' => 'ib39.fr-profiles.index', 'icon' => 'users', 'skyicon' => 'icon-people', 'section' => 'Reintegration'],
                ['label' => 'Record Surfaced FR', 'route' => 'ib39.fr-profiles.create', 'icon' => 'user-plus', 'skyicon' => 'icon-user-follow', 'section' => 'Reintegration'],
                ['label' => 'FEA Processing', 'route' => 'ib39.fea.index', 'icon' => 'document-text', 'skyicon' => 'icon-docs', 'section' => 'Reintegration'],
                ['label' => 'Add Area',  'route' => 'ib39.areas.index', 'icon' => 'map-pin',     'skyicon' => 'icon-map', 'section' => 'Operational Areas'],
                ['label' => 'Map',       'route' => 'ib39.map',         'icon' => 'map',         'skyicon' => 'icon-location-pin', 'section' => 'Operational Areas'],            ],
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
            'nav' => [],
        ],
    ],
];
