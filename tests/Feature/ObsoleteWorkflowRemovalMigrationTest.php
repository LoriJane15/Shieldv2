<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ObsoleteWorkflowRemovalMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_obsolete_workflow_migration_removes_and_restores_only_its_schema(): void
    {
        $migration = require database_path('migrations/2026_09_04_000001_remove_obsolete_eclip_and_japic_workflows.php');

        $migration->down();

        $obsoleteTables = [
            'eclip_livelihood_beneficiary_assistance_histories',
            'eclip_livelihood_beneficiary_assistances',
            'eclip_intervention_histories',
            'eclip_interventions',
            'eclip_reintegration_plan_item_histories',
            'eclip_reintegration_plan_items',
            'eclip_basic_service_documents',
            'eclip_basic_service_histories',
            'eclip_basic_services',
            'eclip_workflow_documents',
            'eclip_workflow_activity_histories',
            'eclip_workflow_activities',
            'eclip_authentication_histories',
            'eclip_authentication_requests',
            'eclip_document_reviews',
            'eclip_document_versions',
            'eclip_documents',
            'eclip_document_requirement_histories',
            'eclip_document_requirements',
            'eclip_assistance_releases',
            'eclip_fund_transactions',
            'eclip_dilg_reviews',
            'eclip_assistance_revisions',
            'eclip_assistance_requests',
            'eclip_assistance_category_histories',
            'eclip_assistance_categories',
            'eclip_liquidation_requirements',
            'eclip_regional_disbursement_reports',
            'eclip_fea_documents',
            'eclip_eligibility_reviews',
            'eclip_status_histories',
            'eclip_report_exports',
            'eclip_case_participants',
            'eclip_cases',
            'lswdo_referrals',
            'mblrc_enrollments',
        ];

        foreach ($obsoleteTables as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertTrue(Schema::hasColumns('fr_government_assistances', ['source_type', 'source_id']));

        $userId = DB::table('users')->insertGetId([
            'username' => 'preserved-japic',
            'name' => 'Synthetic JAPIC Account',
            'password' => 'test',
            'role' => 'japic',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $formerRebelId = DB::table('former_rebels')->insertGetId([
            'classified_id' => 'DISPOSABLE-FR-1',
            'firstname' => 'Synthetic',
            'lastname' => 'Record',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('notifications')->insert([
            [
                'id' => '00000000-0000-0000-0000-000000000001',
                'type' => 'App\\Notifications\\SharedNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $userId,
                'data' => json_encode(['message' => 'Shared notice for a JAPIC account']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '00000000-0000-0000-0000-000000000002',
                'type' => 'App\\Notifications\\EclipCaseActionNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $userId,
                'data' => '{}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        DB::table('audit_logs')->insert([
            ['action' => 'shared', 'entity_type' => 'App\\Models\\FormerRebel', 'entity_id' => $formerRebelId, 'created_at' => now(), 'updated_at' => now()],
            ['action' => 'obsolete', 'entity_type' => 'App\\Models\\MblrcEnrollment', 'entity_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('document_access_logs')->insert([
            ['user_id' => $userId, 'document_type' => 'App\\Models\\Ib39CdrDocument', 'document_id' => 1, 'action' => 'view', 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $userId, 'document_type' => 'App\\Models\\EclipDocument', 'document_id' => 1, 'action' => 'view', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('fr_government_assistances')->insert([
            ['former_rebel_id' => $formerRebelId, 'assistance_type' => 'Shared', 'source_type' => 'App\\Models\\FormerRebel', 'source_id' => $formerRebelId, 'created_at' => now(), 'updated_at' => now()],
            ['former_rebel_id' => $formerRebelId, 'assistance_type' => 'Obsolete', 'source_type' => 'App\\Models\\EclipAssistanceRelease', 'source_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $migration->up();

        foreach ($obsoleteTables as $table) {
            $this->assertFalse(Schema::hasTable($table), $table);
        }

        $this->assertFalse(Schema::hasColumn('fr_government_assistances', 'source_type'));
        $this->assertFalse(Schema::hasColumn('fr_government_assistances', 'source_id'));
        $this->assertSame(1, DB::table('users')->where('username', 'preserved-japic')->count());
        $this->assertSame(1, DB::table('former_rebels')->where('classified_id', 'DISPOSABLE-FR-1')->count());
        $this->assertSame(1, DB::table('notifications')->count());
        $this->assertSame(1, DB::table('audit_logs')->count());
        $this->assertSame(1, DB::table('document_access_logs')->count());
        $this->assertSame(1, DB::table('fr_government_assistances')->count());

        $migration->down();

        foreach ($obsoleteTables as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }

        $this->assertTrue(Schema::hasColumns('fr_government_assistances', ['source_type', 'source_id']));
        $this->assertSame(1, DB::table('users')->where('username', 'preserved-japic')->count());
        $this->assertSame(1, DB::table('former_rebels')->where('classified_id', 'DISPOSABLE-FR-1')->count());

        $migration->up();
    }
}
