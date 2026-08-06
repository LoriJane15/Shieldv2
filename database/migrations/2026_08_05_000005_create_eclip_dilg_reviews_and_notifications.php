<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_dilg_reviews', function (Blueprint $table) {
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

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('eclip_dilg_reviews');
    }
};
