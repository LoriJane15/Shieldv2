<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->string('stage', 30)->index();
            $table->string('title');
            $table->string('provider')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->decimal('amount_or_value', 14, 2)->nullable();
            $table->date('target_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->text('outcome')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
        Schema::create('eclip_intervention_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('eclip_interventions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['intervention_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_intervention_histories');
        Schema::dropIfExists('eclip_interventions');
    }
};
