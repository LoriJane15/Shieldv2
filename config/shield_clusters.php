<?php

// SHIELD 12-cluster rosters (agencies per cluster) — migrated from the
// katuparan_center *_prof.php pages. Keys are the Str::slug() of the wheel
// display names in admin/overview/clusters.blade.php so the wheel links resolve.
// Each agency 'img' is a path relative to public/assets/ (folder included),
// e.g. "basic services/DILG (1).png" or "livelihood/PNP.png".

$shared = [
    ['img' => 'basic services/DILG (1).png', 'acro' => 'DILG', 'name' => 'Department of the Interior and Local Government'],
    ['img' => 'basic services/AFP.png', 'acro' => 'AFP', 'name' => 'Armed Forces of the Philippines'],
    ['img' => 'basic services/DND.png', 'acro' => 'DND', 'name' => 'Department of National Defense'],
    ['img' => 'basic services/DOH.png', 'acro' => 'DOH', 'name' => 'Department of Health'],
    ['img' => 'basic services/DOLE.png', 'acro' => 'DOLE', 'name' => 'Department of Labor and Employment'],
    ['img' => 'basic services/HUDCC.png', 'acro' => 'HUDCC', 'name' => 'Housing and Urban Development Coordinating Council'],
    ['img' => 'basic services/LWUA.png', 'acro' => 'LWUA', 'name' => 'Local Water Utilities Administration'],
    ['img' => 'basic services/NAPC.png', 'acro' => 'NAPC', 'name' => 'National Anti-Poverty Commission'],
    ['img' => 'basic services/NCIP.png', 'acro' => 'NCIP', 'name' => 'National Commission on Indigenous Peoples'],
    ['img' => 'basic services/NEA.png', 'acro' => 'NEA', 'name' => 'National Electrification Administration'],
    ['img' => 'basic services/NEDA.png', 'acro' => 'NEDA', 'name' => 'National Economic and Development Authority'],
    ['img' => 'basic services/NIA.png', 'acro' => 'NIA', 'name' => 'National Irrigation Administration'],
    ['img' => 'basic services/NICA.png', 'acro' => 'NICA', 'name' => 'National Intelligence Coordinating Agency'],
    ['img' => 'basic services/PAGCOR.png', 'acro' => 'PAGCOR', 'name' => 'Philippine Amusement and Gaming Corporation'],
    ['img' => 'basic services/TESDA.png', 'acro' => 'TESDA', 'name' => 'Technical Education and Skills Development Authority'],
    ['img' => 'basic services/NSC.png', 'acro' => 'NSC', 'name' => 'National Security Council'],
];

return [
    'basic-services' => [
        'name' => 'Basic Services',
        'logo' => 'basic services.png',
        'agencies' => $shared,
    ],
    'livelihood' => [
        'name' => 'Poverty Reduction, Livelihood and Employment Cluster',
        'logo' => 'livelihood.png',
        'agencies' => [
            ['img' => 'basic services/DILG (1).png', 'acro' => 'DILG', 'name' => 'Department of the Interior and Local Government'],
            ['img' => 'basic services/AFP.png', 'acro' => 'AFP', 'name' => 'Armed Forces of the Philippines'],
            ['img' => 'basic services/DND.png', 'acro' => 'DND', 'name' => 'Department of National Defense'],
            ['img' => 'livelihood/PNP.png', 'acro' => 'PNP', 'name' => 'Philippine National Police'],
            ['img' => 'basic services/DOLE.png', 'acro' => 'DOLE', 'name' => 'Department of Labor and Employment'],
            ['img' => 'livelihood/DBP.png', 'acro' => 'DBP', 'name' => 'Development Bank of the Philippines'],
            ['img' => 'livelihood/DEPED.png', 'acro' => 'DEPED', 'name' => 'Department of Education'],
            ['img' => 'basic services/NAPC.png', 'acro' => 'NAPC', 'name' => 'National Anti-Poverty Commission'],
            ['img' => 'livelihood/PCA.png', 'acro' => 'PCA', 'name' => 'Philippine Coconut Authority'],
            ['img' => 'livelihood/LBA.png', 'acro' => 'LBP', 'name' => 'Land Bank of the Philippines'],
            ['img' => 'livelihood/DTI.png', 'acro' => 'DTI', 'name' => 'Department of Trade and Industry'],
            ['img' => 'livelihood/DSWD.png', 'acro' => 'DSWD', 'name' => 'Department of Social Welfare and Development'],
            ['img' => 'livelihood/DA.png', 'acro' => 'DA', 'name' => 'Department of Agriculture'],
            ['img' => 'basic services/NICA.png', 'acro' => 'NICA', 'name' => 'National Intelligence Coordinating Agency'],
            ['img' => 'livelihood/NAPC.png', 'acro' => 'NAPC', 'name' => 'National Anti-Poverty Commission'],
            ['img' => 'livelihood/CCC.png', 'acro' => 'CCC', 'name' => 'Climate Change Commission'],
            ['img' => 'basic services/NSC.png', 'acro' => 'NSC', 'name' => 'National Security Council'],
            ['img' => 'livelihood/OPAPRU.png', 'acro' => 'OPAPRU', 'name' => 'Office of the Presidential Adviser on Peace, Reconciliation and Unity'],
            ['img' => 'livelihood/DOST.png', 'acro' => 'DOST', 'name' => 'Department of Science and Technology'],
            ['img' => 'livelihood/CDA.png', 'acro' => 'CDA', 'name' => 'Cooperative Development Authority'],
            ['img' => 'livelihood/DAR.png', 'acro' => 'DAR', 'name' => 'Department of Agrarian Reform'],
            ['img' => 'livelihood/DENR.png', 'acro' => 'DENR', 'name' => 'Department of Environment and Natural Resources'],
        ],
    ],
    'comprehensive' => [
        'name' => 'Comprehensive Program Cluster',
        'logo' => 'comprehensive.png',
        'agencies' => $shared,
    ],
    'cooperation' => [
        'name' => 'Legal Cooperation Cluster',
        'logo' => 'cooperation.png',
        'agencies' => $shared,
    ],
    'empowerment' => [
        'name' => 'Local Government Empowerment Cluster',
        'logo' => 'empowerment.png',
        'agencies' => $shared,
    ],
    'enforcement' => [
        'name' => 'Peace, Law Enforcement, and Development Support Cluster',
        'logo' => 'enforcement.png',
        'agencies' => $shared,
    ],
    'infrastructure' => [
        'name' => 'Infrastructure & Resource Management Cluster',
        'logo' => 'infrastructure.png',
        'agencies' => $shared,
    ],
    'international' => [
        'name' => 'International Engagement Cluster',
        'logo' => 'international.png',
        'agencies' => $shared,
    ],
    'local-peace' => [
        'name' => 'Localized Peace Engagement Cluster',
        'logo' => 'local peace.png',
        'agencies' => $shared,
    ],
    'sectoral' => [
        'name' => 'Sectoral Unification, Capacity-Building, Empowerment, and Mobilization Cluster',
        'logo' => 'sectoral.png',
        'agencies' => $shared,
    ],
    'situational' => [
        'name' => 'Situational Awareness and Knowledge Management',
        'logo' => 'situational.png',
        'agencies' => $shared,
    ],
    'strategic' => [
        'name' => 'Strategic Communication Cluster',
        'logo' => 'strategic.png',
        'agencies' => $shared,
    ],
];
