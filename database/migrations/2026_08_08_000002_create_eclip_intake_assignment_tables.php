<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mblrc_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('former_rebel_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->default('in_progress')->index();
            $table->date('integration_started_at')->nullable();
            $table->date('integration_completed_at')->nullable();
            $table->foreignId('verified_municipality_id')->nullable()->constrained('municipalities')->nullOnDelete();
            $table->text('location_verification_remarks')->nullable();
            $table->json('phase_one_evidence')->nullable();
            $table->timestamps();
        });

        Schema::create('lswdo_referrals', function (Blueprint $table) {
            $table->id();
            $table->string('referral_number', 40)->unique();
            $table->foreignId('mblrc_enrollment_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('former_rebel_id')->constrained()->restrictOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('referred_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['assigned_to', 'status']);
            $table->index(['municipality_id', 'status']);
        });

        Schema::table('eclip_cases', function (Blueprint $table) {
            $table->foreignId('lswdo_referral_id')->nullable()->unique()->after('former_rebel_id')
                ->constrained('lswdo_referrals')->nullOnDelete();
        });

        Schema::create('eclip_case_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('participant_role', 50);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['eclip_case_id', 'user_id', 'participant_role'], 'eclip_case_participant_unique');
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_case_participants');
        Schema::table('eclip_cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lswdo_referral_id');
        });
        Schema::dropIfExists('lswdo_referrals');
        Schema::dropIfExists('mblrc_enrollments');
    }
};
