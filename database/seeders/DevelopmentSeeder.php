<?php

namespace Database\Seeders;

use App\Enums\EclipCaseStatus;
use App\Models\AgencyImplanResponse;
use App\Models\Barangay;
use App\Models\EclipAssistanceCategory;
use App\Models\EclipAssistanceRequest;
use App\Models\EclipCase;
use App\Models\FormerRebel;
use App\Models\GovAgency;
use App\Models\Implementation;
use App\Models\ImplementationTagging;
use App\Models\MapBarangay;
use App\Models\Municipality;
use App\Models\RcspActivity;
use App\Models\RcspBarangay;
use App\Models\RcspForm;
use App\Models\RcspPhase;
use App\Models\RcspPhaseStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('DevelopmentSeeder may only run in local or testing environments.');
        }

        $password = (string) config('shield.development_seed_password');

        if (strlen($password) < 12) {
            throw new RuntimeException(
                'Set SHIELD_DEV_PASSWORD in .env to at least 12 characters before seeding development data.'
            );
        }

        DB::transaction(function () use ($password): void {
            $agencies = $this->seedAgencies();
            $users = $this->seedUsers($password, $agencies);
            $phases = $this->seedRcspReferenceData();
            $rcspBarangays = $this->seedRcspWorkflow($users['lgu'], $phases);

            $this->seedImplementationWorkflow($users['lgu'], $agencies, $rcspBarangays);
            $this->seedFormerRebelSamples();
            $this->seedEclipSamples($users);
            $this->seedMapSamples();
        });
    }

    /**
     * @return array<string, GovAgency>
     */
    private function seedAgencies(): array
    {
        return collect([
            ['key' => 'alpha', 'name' => 'Synthetic Test Agency Alpha', 'acronym' => 'TEST-A'],
            ['key' => 'beta', 'name' => 'Synthetic Test Agency Beta', 'acronym' => 'TEST-B'],
        ])->mapWithKeys(function (array $attributes): array {
            $agency = GovAgency::query()->updateOrCreate(
                ['acronym' => $attributes['acronym']],
                ['name' => $attributes['name']]
            );
            if ($agency->profile === 'Synthetic development record') {
                $agency->update(['profile' => null]);
            }

            return [$attributes['key'] => $agency];
        })->all();
    }

    /**
     * @param  array<string, GovAgency>  $agencies
     * @return array<string, User>
     */
    private function seedUsers(string $password, array $agencies): array
    {
        $municipality = Municipality::query()->where('name', 'Digos City')->firstOrFail();
        $passwordHash = Hash::make($password);

        $accounts = [
            'super_admin' => ['name' => 'Test Super Administrator', 'role' => 'super_admin'],
            'admin' => ['name' => 'Test Katuparan Administrator', 'role' => 'admin'],
            '39th_ib' => ['name' => 'Test 39th IB User', 'role' => '39th_ib'],
            'lgu' => [
                'name' => 'Test Digos LGU User',
                'role' => 'lgu',
                'municipality_id' => $municipality->id,
            ],
            'gov_agency' => [
                'name' => 'Test Agency Alpha User',
                'role' => 'gov_agency',
                'gov_agency_id' => $agencies['alpha']->id,
            ],
            'mblrc' => ['name' => 'Test MBLRC User', 'role' => 'mblrc'],
            'lswdo' => [
                'name' => 'Test Digos LSWDO User',
                'role' => 'lswdo',
                'municipality_id' => $municipality->id,
            ],
            'japic' => ['name' => 'Test JAPIC User', 'role' => 'japic'],
            'dilg_provincial_focal' => [
                'name' => 'Test Digos DILG Provincial Focal Person',
                'role' => 'dilg_provincial_focal',
                'municipality_id' => $municipality->id,
            ],
            'dilg_regional' => ['name' => 'Test DILG Regional Office User', 'role' => 'dilg_regional'],
            'nboo_eclip_pmo' => ['name' => 'Test NBOO ECLIP-PMO User', 'role' => 'nboo_eclip_pmo'],
            'dilg_fms' => ['name' => 'Test DILG FMS User', 'role' => 'dilg_fms'],
            'local_eclip_committee' => [
                'name' => 'Test Digos Local E-CLIP Committee',
                'role' => 'local_eclip_committee',
                'municipality_id' => $municipality->id,
            ],
            'pnp' => ['name' => 'Test PNP User', 'role' => 'pnp'],
            'afp' => ['name' => 'Test AFP User', 'role' => 'afp'],
        ];

        return collect($accounts)->mapWithKeys(function (array $attributes, string $username) use ($passwordHash): array {
            $user = User::query()->updateOrCreate(
                ['username' => $username],
                [
                    ...$attributes,
                    'email' => str_replace('_', '.', $username).'@example.invalid',
                    'password' => $passwordHash,
                ]
            );
            $user->forceFill(['email_verified_at' => now()])->save();

            return [$username => $user];
        })->all();
    }

    /**
     * @return array<int, RcspPhase>
     */
    private function seedRcspReferenceData(): array
    {
        $phases = [];

        foreach (range(0, 5) as $number) {
            $phase = RcspPhase::query()->updateOrCreate(
                ['number' => $number],
                ['name' => "Synthetic Test Phase {$number}"]
            );

            RcspActivity::query()->updateOrCreate(
                [
                    'rcsp_phase_id' => $phase->id,
                    'description' => "Synthetic test activity for phase {$number}",
                ],
                []
            );

            $phases[$number] = $phase;
        }

        return $phases;
    }

    /**
     * @param  array<int, RcspPhase>  $phases
     * @return array<string, RcspBarangay>
     */
    private function seedRcspWorkflow(User $lguUser, array $phases): array
    {
        $barangays = Barangay::query()
            ->where('municipality_id', $lguUser->municipality_id)
            ->orderBy('id')
            ->limit(3)
            ->get();

        if ($barangays->count() < 3) {
            throw new RuntimeException('Development seeding requires at least three barangays for the LGU municipality.');
        }

        $states = [
            'pending' => ['status' => 'Pending', 'current_phase' => 0],
            'ongoing' => ['status' => 'Ongoing', 'current_phase' => 1],
            'completed' => ['status' => 'Completed', 'current_phase' => 5],
        ];

        $records = [];

        foreach ($states as $key => $state) {
            $barangay = $barangays->shift();
            $record = RcspBarangay::query()->updateOrCreate(
                ['barangay_id' => $barangay->id, 'municipality_id' => $lguUser->municipality_id],
                $state
            );

            $completed = $key === 'completed' ? 5 : ($key === 'ongoing' ? 0 : -1);
            $phaseStatus = ['rcsp_barangay_id' => $record->id];

            foreach (range(0, 5) as $phaseNumber) {
                $phaseStatus["phase{$phaseNumber}_completed"] = $phaseNumber <= $completed;
            }

            RcspPhaseStatus::query()->updateOrCreate(
                ['rcsp_barangay_id' => $record->id],
                $phaseStatus
            );

            if ($key !== 'pending') {
                $phaseNumber = $key === 'completed' ? 5 : 0;
                $activity = $phases[$phaseNumber]->activities()->firstOrFail();

                RcspForm::query()->updateOrCreate(
                    [
                        'lgu_user_id' => $lguUser->id,
                        'rcsp_barangay_id' => $record->id,
                        'rcsp_phase_id' => $phases[$phaseNumber]->id,
                        'rcsp_activity_id' => $activity->id,
                    ],
                    [
                        'conduct' => 'yes',
                        'status' => $key === 'completed' ? 'approved' : 'submitted',
                        'remarks' => 'Synthetic development record; no official document attached.',
                    ]
                );
            }

            $records[$key] = $record;
        }

        return $records;
    }

    /**
     * @param  array<string, GovAgency>  $agencies
     * @param  array<string, RcspBarangay>  $rcspBarangays
     */
    private function seedImplementationWorkflow(User $lguUser, array $agencies, array $rcspBarangays): void
    {
        $states = [
            'not yet started' => null,
            'ongoing' => 'accepted',
            'for verification' => 'pending',
            'verified' => 'accepted',
        ];

        foreach ($states as $status => $responseStatus) {
            $implementation = Implementation::query()->updateOrCreate(
                [
                    'lgu_user_id' => $lguUser->id,
                    'issues' => "Synthetic test issue ({$status})",
                ],
                [
                    'uploaded_at' => now()->toDateString(),
                    'program' => 'Synthetic testing program',
                    'target_areas' => [$rcspBarangays['ongoing']->id],
                    'agencies' => [$agencies['alpha']->id],
                    'beneficiaries' => 'Synthetic beneficiaries only',
                    'outcome' => 'Exercise the development workflow',
                    'resources' => 'Synthetic resources',
                    'support' => 'Synthetic support',
                    'duration' => 'Test duration',
                    'status' => $status,
                    'type_gov' => 'NGA',
                    'sources' => 'DevelopmentSeeder',
                    'remarks' => 'Not an official government record.',
                ]
            );

            if ($responseStatus === null) {
                continue;
            }

            AgencyImplanResponse::query()->updateOrCreate(
                [
                    'gov_agency_id' => $agencies['alpha']->id,
                    'implementation_id' => $implementation->id,
                ],
                ['response_status' => $responseStatus, 'rejection_reason' => null]
            );

            ImplementationTagging::query()->updateOrCreate(
                [
                    'implementation_id' => $implementation->id,
                    'gov_agency_id' => $agencies['alpha']->id,
                ],
                ['status' => ucfirst($responseStatus), 'reason' => null]
            );
        }
    }

    private function seedFormerRebelSamples(): void
    {
        $municipality = Municipality::query()->where('name', 'Digos City')->firstOrFail();
        $barangays = $municipality->barangays()->orderBy('id')->limit(3)->get();
        $statuses = ['Active', 'Under Review', 'Reintegrated'];
        $coordinates = [
            ['latitude' => 6.7497, 'longitude' => 125.3572],
            ['latitude' => 6.7314, 'longitude' => 125.3496],
            ['latitude' => 6.7652, 'longitude' => 125.3721],
        ];

        foreach ($statuses as $index => $status) {
            $barangay = $barangays[$index];
            $record = FormerRebel::query()->updateOrCreate(
                ['classified_id' => 'FR-#'.str_pad((string) (9001 + $index), 4, '0', STR_PAD_LEFT)],
                [
                    'firstname' => 'Synthetic',
                    'middlename' => 'Test',
                    'lastname' => 'Person '.($index + 1),
                    'gender' => $index % 2 === 0 ? 'Male' : 'Female',
                    'age' => 30 + $index,
                    'civil_status' => 'Single',
                    'residential_address' => 'Synthetic address for development testing',
                    'placement_address' => $barangay->name.', Digos City',
                    'batch_year' => 'TEST-2026',
                    'batch_section' => '1',
                    'barangay_id' => $barangay->id,
                    'municipality_id' => $municipality->id,
                    'province' => 'Davao del Sur',
                    'registered_at' => now()->subDays($index)->toDateString(),
                    'status' => $status,
                    'latitude' => $coordinates[$index]['latitude'],
                    'longitude' => $coordinates[$index]['longitude'],
                    'occupation' => 'Synthetic occupation',
                    'work_status' => 'Test record',
                ]
            );

            $record->programStatus()->updateOrCreate(
                [],
                [
                    'reintegration_status' => ['On-going', 'Not-Started', 'Completed'][$index],
                    'reintegration_date' => $index === 2 ? now()->toDateString() : null,
                    'updated_by' => 'DevelopmentSeeder',
                ]
            );

            $record->skills()->updateOrCreate(
                ['skill_name' => 'Synthetic test skill'],
                ['proficiency_level' => 'Intermediate']
            );

            $record->assistances()->updateOrCreate(
                ['assistance_type' => 'Synthetic test assistance'],
                [
                    'date_received' => $index === 2 ? now()->toDateString() : null,
                    'status' => $index === 2 ? 'Completed' : 'Pending',
                ]
            );
        }
    }

    /**
     * Create synthetic E-CLIP records across the real workflow queues.
     *
     * @param  array<string, User>  $users
     */
    private function seedEclipSamples(array $users): void
    {
        $municipality = Municipality::query()->where('name', 'Digos City')->firstOrFail();
        $barangays = $municipality->barangays()->orderBy('id')->limit(3)->get();

        if ($barangays->isEmpty()) {
            throw new RuntimeException('E-CLIP development seeding requires at least one Digos City barangay.');
        }

        $category = EclipAssistanceCategory::query()->updateOrCreate(
            ['code' => 'DEMO-LIVELIHOOD'],
            [
                'name' => 'Synthetic Livelihood Assistance',
                'description' => 'Development-only assistance category; not an official program record.',
                'is_active' => true,
                'sort_order' => 900,
            ]
        );

        $samples = [
            EclipCaseStatus::SubmittedForEligibility,
            EclipCaseStatus::SubmittedForEligibility,
            EclipCaseStatus::Eligible,
            EclipCaseStatus::DocumentsCertified,
            EclipCaseStatus::AssistanceAssessment,
            EclipCaseStatus::SubmittedForDilgReview,
            EclipCaseStatus::ProvincialEndorsed,
            EclipCaseStatus::RegionalEndorsed,
            EclipCaseStatus::Approved,
            EclipCaseStatus::FundsTransferred,
        ];

        foreach ($samples as $index => $status) {
            $number = $index + 1;
            $barangay = $barangays[$index % $barangays->count()];
            $formerRebel = FormerRebel::query()->updateOrCreate(
                ['classified_id' => 'FR-#D'.str_pad((string) $number, 3, '0', STR_PAD_LEFT)],
                [
                    'firstname' => 'Demo',
                    'middlename' => 'Synthetic',
                    'lastname' => 'Beneficiary '.$number,
                    'gender' => $index % 2 === 0 ? 'Male' : 'Female',
                    'age' => 25 + $index,
                    'civil_status' => 'Single',
                    'residential_address' => 'Synthetic development address',
                    'placement_address' => $barangay->name.', Digos City',
                    'batch_year' => 'DEMO-2026',
                    'batch_section' => '1',
                    'barangay_id' => $barangay->id,
                    'municipality_id' => $municipality->id,
                    'province' => 'Davao del Sur',
                    'registered_at' => now()->subDays(20 - $index)->toDateString(),
                    'status' => 'Active',
                    'latitude' => 6.7200 + ($index * 0.0060),
                    'longitude' => 125.3300 + ($index * 0.0070),
                    'occupation' => 'Synthetic occupation',
                    'work_status' => 'Development-only record',
                ]
            );

            $case = EclipCase::query()->updateOrCreate(
                ['case_number' => 'ECLIP-DEMO-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT)],
                [
                    'former_rebel_id' => $formerRebel->id,
                    'municipality_id' => $municipality->id,
                    'created_by' => $users['mblrc']->id,
                    'assigned_to' => $users['lswdo']->id,
                    'status' => $status,
                    'submitted_at' => now()->subDays(15 - $index),
                    'eligibility_decided_at' => $index >= 2 ? now()->subDays(13 - min($index, 9)) : null,
                ]
            );

            $case->statusHistories()->updateOrCreate(
                ['to_status' => $status->value],
                [
                    'user_id' => $this->actorForStatus($status, $users)->id,
                    'from_status' => $status === EclipCaseStatus::SubmittedForEligibility ? EclipCaseStatus::Draft->value : null,
                    'remarks' => 'Synthetic workflow state created by DevelopmentSeeder.',
                    'ip_address' => '127.0.0.1',
                ]
            );

            if ($index >= 2) {
                $case->eligibilityReviews()->updateOrCreate(
                    ['reviewed_by' => $users['lswdo']->id, 'decision' => 'eligible'],
                    ['remarks' => 'Synthetic eligibility review.', 'reviewed_at' => now()->subDays(12)]
                );
            }

            if ($index < 4) {
                continue;
            }

            $requestStatus = match ($status) {
                EclipCaseStatus::AssistanceAssessment => 'draft',
                EclipCaseStatus::Approved, EclipCaseStatus::FundsTransferred => 'approved',
                default => 'submitted',
            };
            $assistanceRequest = EclipAssistanceRequest::query()->updateOrCreate(
                ['eclip_case_id' => $case->id],
                [
                    'created_by' => $users['lswdo']->id,
                    'status' => $requestStatus,
                    'submitted_at' => $requestStatus === 'draft' ? null : now()->subDays(8),
                ]
            );
            $amount = 25000 + ($index * 5000);
            $revision = $assistanceRequest->revisions()->updateOrCreate(
                ['revision_number' => 1],
                [
                    'category_id' => $category->id,
                    'requested_amount' => $amount,
                    'assessed_amount' => $amount - 2500,
                    'justification' => 'Synthetic development justification for UI and workflow testing.',
                    'assessment_remarks' => 'Development-only assessment; not an official determination.',
                    'created_by' => $users['lswdo']->id,
                ]
            );

            if ($index >= 6) {
                $this->seedDilgReview($case, $assistanceRequest, $revision->id, $users['dilg_provincial_focal'], 'provincial', 'endorsed');
            }
            if ($index >= 7) {
                $this->seedDilgReview($case, $assistanceRequest, $revision->id, $users['dilg_regional'], 'regional', 'endorsed');
            }
            if ($index >= 8) {
                $this->seedDilgReview($case, $assistanceRequest, $revision->id, $users['nboo_eclip_pmo'], 'national', 'approved');
            }
            if ($status === EclipCaseStatus::FundsTransferred) {
                foreach (['allocation', 'transfer'] as $type) {
                    $case->fundTransactions()->updateOrCreate(
                        ['type' => $type, 'reference_number' => 'DEMO-'.strtoupper($type).'-'.$number],
                        [
                            'assistance_request_id' => $assistanceRequest->id,
                            'assistance_revision_id' => $revision->id,
                            'amount' => $amount - 2500,
                            'transaction_date' => now()->subDays($type === 'allocation' ? 3 : 2)->toDateString(),
                            'remarks' => 'Synthetic development funding transaction.',
                            'created_by' => $users['dilg_fms']->id,
                        ]
                    );
                }
            }
        }
    }

    /** @param array<string, User> $users */
    private function actorForStatus(EclipCaseStatus $status, array $users): User
    {
        return match ($status) {
            EclipCaseStatus::SubmittedForEligibility => $users['mblrc'],
            EclipCaseStatus::Eligible, EclipCaseStatus::DocumentsCertified,
            EclipCaseStatus::AssistanceAssessment, EclipCaseStatus::SubmittedForDilgReview => $users['lswdo'],
            EclipCaseStatus::ProvincialEndorsed => $users['dilg_provincial_focal'],
            EclipCaseStatus::RegionalEndorsed => $users['dilg_regional'],
            EclipCaseStatus::Approved => $users['nboo_eclip_pmo'],
            EclipCaseStatus::FundsTransferred => $users['dilg_fms'],
            default => $users['mblrc'],
        };
    }

    private function seedDilgReview(
        EclipCase $case,
        EclipAssistanceRequest $request,
        int $revisionId,
        User $reviewer,
        string $level,
        string $decision,
    ): void {
        $case->dilgReviews()->updateOrCreate(
            ['review_level' => $level, 'assistance_revision_id' => $revisionId],
            [
                'assistance_request_id' => $request->id,
                'reviewed_by' => $reviewer->id,
                'decision' => $decision,
                'feedback' => 'Synthetic development review feedback.',
                'reviewed_at' => now()->subDays(match ($level) {
                    'provincial' => 7,
                    'regional' => 6,
                    default => 5,
                }),
            ]
        );
    }

    private function seedMapSamples(): void
    {
        $samples = [
            ['barangay' => 'Synthetic Recovery Area', 'frs' => 5],
            ['barangay' => 'Synthetic Expansion Area', 'frs' => 12],
            ['barangay' => 'Synthetic Consolidated Area', 'frs' => 22],
        ];

        foreach ($samples as $index => $sample) {
            $classification = MapBarangay::classify($sample['frs']);

            $area = MapBarangay::query()->updateOrCreate(
                ['fid' => 'TEST-'.($index + 1)],
                [
                    'province' => 'Davao del Sur',
                    'municipality' => 'Digos City',
                    'barangay' => $sample['barangay'],
                    'frs' => $sample['frs'],
                    'status' => $classification['status'],
                    'infestation_color' => $classification['color'],
                    'rebels' => 0,
                ]
            );

            $area->colorHistories()->updateOrCreate(
                ['status' => $classification['status'], 'frs' => $sample['frs']],
                ['color' => $classification['color']]
            );
        }
    }
}
