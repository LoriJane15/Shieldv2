<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_liquidation_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->string('assistance_category');
            $table->string('requirement_name');
            $table->string('status', 30)->default('missing')->index();
            $table->string('reference')->nullable();
            $table->date('submitted_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->date('resubmitted_at')->nullable();
            $table->date('accepted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['eclip_case_id', 'assistance_category']);
        });
        Schema::create('eclip_regional_disbursement_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->date('reporting_month');
            $table->string('form_11_reference')->nullable();
            $table->string('status', 30)->default('preparing')->index();
            $table->date('submitted_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->date('resubmitted_at')->nullable();
            $table->date('accepted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['eclip_case_id', 'reporting_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_regional_disbursement_reports');
        Schema::dropIfExists('eclip_liquidation_requirements');
    }
};
