<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private const TABLES = [
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

    public function up(): void
    {
        $this->removePolymorphicRecords();

        Schema::withoutForeignKeyConstraints(function (): void {
            foreach (self::TABLES as $table) {
                Schema::dropIfExists($table);
            }
        });

        if (Schema::hasTable('fr_government_assistances')) {
            Schema::table('fr_government_assistances', function (Blueprint $table): void {
                if ($this->hasIndex('fr_government_assistances', 'fr_assistance_source_unique')) {
                    $table->dropUnique('fr_assistance_source_unique');
                }
                $columns = array_values(array_filter(
                    ['source_type', 'source_id'],
                    fn (string $column): bool => Schema::hasColumn('fr_government_assistances', $column)
                ));
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }

    public function down(): void
    {
        $this->runHistoricalMigration('2026_08_05_000002_create_eclip_case_tables.php');
        $this->runHistoricalMigration('2026_08_05_000003_create_eclip_document_tables.php');
        $this->runHistoricalMigration('2026_08_05_000004_create_eclip_assessment_tables.php');
        $this->createDilgReviews();
        $this->runHistoricalMigration('2026_08_05_000006_create_eclip_fund_transactions.php');
        $this->runHistoricalMigration('2026_08_05_000007_create_eclip_assistance_releases.php');
        $this->runHistoricalMigration('2026_08_05_000008_create_eclip_report_exports.php');
        $this->runHistoricalMigration('2026_08_05_000010_create_eclip_basic_service_tables.php');
        $this->runHistoricalMigration('2026_08_06_000001_create_eclip_workflow_activity_tables.php');
        $this->runHistoricalMigration('2026_08_08_000002_create_eclip_intake_assignment_tables.php');
        $this->runHistoricalMigration('2026_08_08_000003_create_eclip_authentication_requests.php');
        $this->runHistoricalMigration('2026_08_24_000001_create_eclip_fea_documents_table.php');
        $this->runHistoricalMigration('2026_08_24_000002_create_eclip_interventions_tables.php');
        $this->runHistoricalMigration('2026_08_24_000003_create_eclip_liquidation_and_disbursement_tables.php');
        $this->restoreLaterSchemaChanges();
    }

    private function removePolymorphicRecords(): void
    {
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('type', 'App\\Notifications\\EclipCaseActionNotification')
                ->delete();
        }

        if (Schema::hasTable('audit_logs')) {
            $this->deletedModelRecords('audit_logs', 'entity_type')->delete();
        }

        if (Schema::hasTable('document_access_logs')) {
            $this->deletedModelRecords('document_access_logs', 'document_type')->delete();
        }

        if (Schema::hasTable('fr_government_assistances')
            && Schema::hasColumn('fr_government_assistances', 'source_type')) {
            $this->deletedModelRecords('fr_government_assistances', 'source_type')->delete();
        }
    }

    private function deletedModelRecords(string $table, string $column): Builder
    {
        return DB::table($table)->where(function ($query) use ($column): void {
            $query->where($column, 'like', 'App\\Models\\Eclip%')
                ->orWhereIn($column, [
                    'App\\Models\\LswdoReferral',
                    'App\\Models\\MblrcEnrollment',
                ]);
        });
    }

    private function restoreLaterSchemaChanges(): void
    {
        Schema::table('eclip_document_versions', function (Blueprint $table): void {
            $table->string('classification', 30)->default('confidential')->index();
            $table->string('status', 30)->default('uploaded')->index();
            $table->timestamp('submitted_at')->nullable();
        });

        Schema::table('eclip_dilg_reviews', function (Blueprint $table): void {
            $table->string('review_level', 30)->nullable()->index();
        });

        Schema::create('eclip_workflow_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')->constrained('eclip_workflow_activities')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('document_type');
            $table->unsignedInteger('version_number');
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['activity_id', 'document_type', 'version_number'], 'eclip_workflow_document_version_unique');
            $table->index(['activity_id', 'document_type']);
        });

        Schema::table('eclip_workflow_activity_histories', function (Blueprint $table): void {
            $table->string('event', 40)->default('status_changed');
            $table->string('actor_role', 80)->nullable();
            $table->string('actor_office')->nullable();
            $table->unsignedBigInteger('document_id')->nullable()->index();
        });

        Schema::table('fr_government_assistances', function (Blueprint $table): void {
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unique(['source_type', 'source_id'], 'fr_assistance_source_unique');
        });

        $this->runHistoricalMigration('2026_08_26_000001_align_eclip_workflow_records.php');
    }

    private function createDilgReviews(): void
    {
        Schema::create('eclip_dilg_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assistance_request_id')->constrained('eclip_assistance_requests')->restrictOnDelete();
            $table->foreignId('assistance_revision_id')->constrained('eclip_assistance_revisions')->restrictOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30);
            $table->text('feedback')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->index(['eclip_case_id', 'reviewed_at'], 'eclip_dilg_case_reviewed_index');
        });
    }

    private function runHistoricalMigration(string $file): void
    {
        $migration = require database_path('migrations/'.$file);
        $migration->up();
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(
            fn (array $definition): bool => $definition['name'] === $index
        );
    }
};
