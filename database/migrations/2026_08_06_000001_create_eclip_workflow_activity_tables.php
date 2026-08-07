<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_workflow_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->string('step_code', 10);
            $table->unsignedTinyInteger('phase');
            $table->string('title');
            $table->string('status', 30)->default('locked');
            $table->json('responsible_roles');
            $table->json('required_documents')->nullable();
            $table->timestamp('available_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();

            $table->unique(['eclip_case_id', 'step_code']);
            $table->index(['status', 'due_at']);
        });

        Schema::create('eclip_workflow_activity_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained('eclip_workflow_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('remarks')->nullable();
            $table->json('data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['activity_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_workflow_activity_histories');
        Schema::dropIfExists('eclip_workflow_activities');
    }
};
