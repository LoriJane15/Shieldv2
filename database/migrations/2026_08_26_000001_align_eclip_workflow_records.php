<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eclip_eligibility_reviews', function (Blueprint $table) {
            $table->string('referral_status', 30)->nullable()->after('decision');
            $table->string('referred_program')->nullable()->after('referral_status');
        });

        Schema::table('eclip_assistance_releases', function (Blueprint $table) {
            $table->string('recipient')->nullable()->after('released_at');
            $table->timestamp('received_confirmed_at')->nullable()->after('remarks');
            $table->foreignId('received_confirmed_by')->nullable()->after('received_confirmed_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('eclip_reintegration_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->text('identified_need');
            $table->string('proposed_assistance');
            $table->string('responsible_agency')->nullable();
            $table->string('lgu_counterpart')->nullable();
            $table->string('form_of_assistance')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('target_date')->nullable();
            $table->string('status', 30)->default('planned')->index();
            $table->text('partner_agencies')->nullable();
            $table->text('agency_commitments')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['eclip_case_id', 'status']);
        });

        Schema::create('eclip_reintegration_plan_item_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_item_id')->constrained('eclip_reintegration_plan_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['plan_item_id', 'created_at']);
        });

        Schema::table('eclip_interventions', function (Blueprint $table) {
            $table->foreignId('reintegration_plan_item_id')->nullable()->after('eclip_case_id')->constrained('eclip_reintegration_plan_items')->nullOnDelete();
            $table->date('referral_date')->nullable()->after('provider');
            $table->string('evidence_reference')->nullable()->after('outcome');
            $table->string('receiving_agency')->nullable()->after('evidence_reference');
        });

        Schema::create('eclip_livelihood_beneficiary_assistances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->text('implementation_reason');
            $table->text('beneficiary_name');
            $table->string('relationship');
            $table->string('approval_reference');
            $table->string('approval_status', 30)->default('pending');
            $table->decimal('assistance_amount', 15, 2)->nullable();
            $table->string('release_status', 30)->default('pending');
            $table->date('release_date')->nullable();
            $table->string('supporting_reference')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['eclip_case_id', 'release_status'], 'eclip_livelihood_case_release_index');
        });

        Schema::create('eclip_livelihood_beneficiary_assistance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistance_id')->constrained('eclip_livelihood_beneficiary_assistances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['assistance_id', 'created_at'], 'eclip_livelihood_history_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_livelihood_beneficiary_assistance_histories');
        Schema::dropIfExists('eclip_livelihood_beneficiary_assistances');

        Schema::table('eclip_interventions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reintegration_plan_item_id');
            $table->dropColumn(['referral_date', 'evidence_reference', 'receiving_agency']);
        });

        Schema::dropIfExists('eclip_reintegration_plan_item_histories');
        Schema::dropIfExists('eclip_reintegration_plan_items');

        Schema::table('eclip_assistance_releases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('received_confirmed_by');
            $table->dropColumn(['recipient', 'received_confirmed_at']);
        });

        Schema::table('eclip_eligibility_reviews', function (Blueprint $table) {
            $table->dropColumn(['referral_status', 'referred_program']);
        });
    }
};
