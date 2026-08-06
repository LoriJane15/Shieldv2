<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->nullable()->unique();
            $table->foreignId('former_rebel_id')->constrained()->restrictOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 60)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('eligibility_decided_at')->nullable();
            $table->timestamps();

            $table->index(['municipality_id', 'status']);
        });

        Schema::create('eclip_eligibility_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30);
            $table->text('remarks')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->index(['eclip_case_id', 'reviewed_at']);
        });

        Schema::create('eclip_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('from_status', 60)->nullable();
            $table->string('to_status', 60);
            $table->text('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['eclip_case_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_status_histories');
        Schema::dropIfExists('eclip_eligibility_reviews');
        Schema::dropIfExists('eclip_cases');
    }
};
