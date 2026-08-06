<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_assistance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('eclip_assistance_category_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('eclip_assistance_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->json('new_values');
            $table->timestamps();
        });

        Schema::create('eclip_assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('eclip_assistance_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistance_request_id')->constrained('eclip_assistance_requests')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('eclip_assistance_categories')->restrictOnDelete();
            $table->unsignedInteger('revision_number');
            $table->decimal('requested_amount', 15, 2);
            $table->decimal('assessed_amount', 15, 2)->nullable();
            $table->text('justification');
            $table->text('assessment_remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(
                ['assistance_request_id', 'revision_number'],
                'eclip_assistance_revision_number_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_assistance_revisions');
        Schema::dropIfExists('eclip_assistance_requests');
        Schema::dropIfExists('eclip_assistance_category_histories');
        Schema::dropIfExists('eclip_assistance_categories');
    }
};
