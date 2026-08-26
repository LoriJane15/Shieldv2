<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_workflow_documents', function (Blueprint $table) {
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

        Schema::table('eclip_workflow_activity_histories', function (Blueprint $table) {
            $table->string('event', 40)->default('status_changed')->after('user_id');
            $table->string('actor_role', 80)->nullable()->after('event');
            $table->string('actor_office')->nullable()->after('actor_role');
            $table->unsignedBigInteger('document_id')->nullable()->after('data')->index();
        });

        Schema::table('fr_government_assistances', function (Blueprint $table) {
            $table->decimal('amount_or_value', 15, 2)->nullable()->after('assistance_type');
            $table->string('provider')->nullable()->after('amount_or_value');
            $table->text('remarks')->nullable()->after('status');
            $table->string('source_type', 50)->nullable()->after('certificate_file');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->unique(['source_type', 'source_id'], 'fr_assistance_source_unique');
        });

        DB::table('eclip_workflow_activities')->whereIn('step_code', [
            '6A', '6B', '6C', '6D', '6E', '6F', '6G', '6H', '6I', '7A', '7B', '8A', '9',
        ])->update(['phase' => 3]);
        DB::table('eclip_workflow_activities')->whereIn('step_code', ['10', '11', '12', '13', '14'])->update(['phase' => 4]);
    }

    public function down(): void
    {
        DB::table('eclip_workflow_activities')->whereIn('step_code', ['6A', '6B', '6C', '6D', '6E', '6F', '6G', '6H', '6I'])->update(['phase' => 2]);
        DB::table('eclip_workflow_activities')->whereIn('step_code', ['7A', '7B'])->update(['phase' => 3]);
        DB::table('eclip_workflow_activities')->whereIn('step_code', ['8A', '9'])->update(['phase' => 4]);
        DB::table('eclip_workflow_activities')->whereIn('step_code', ['10', '11', '12', '13', '14'])->update(['phase' => 5]);

        Schema::table('fr_government_assistances', function (Blueprint $table) {
            $table->dropUnique('fr_assistance_source_unique');
            $table->dropColumn(['amount_or_value', 'provider', 'remarks', 'source_type', 'source_id']);
        });

        Schema::table('eclip_workflow_activity_histories', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropColumn(['event', 'actor_role', 'actor_office', 'document_id']);
        });

        Schema::dropIfExists('eclip_workflow_documents');
    }
};
