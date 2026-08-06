<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('eclip_document_requirement_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained('eclip_document_requirements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->json('new_values');
            $table->timestamps();
        });

        Schema::create('eclip_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('eclip_document_requirements')->restrictOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamps();
            $table->unique(['eclip_case_id', 'requirement_id']);
        });

        Schema::create('eclip_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['eclip_document_id', 'version_number']);
        });

        Schema::create('eclip_document_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_version_id')->constrained('eclip_document_versions')->restrictOnDelete();
            $table->foreignId('reviewed_by')->constrained('users')->restrictOnDelete();
            $table->string('decision', 30);
            $table->text('remarks')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();
            $table->index(['eclip_document_id', 'reviewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_document_reviews');
        Schema::dropIfExists('eclip_document_versions');
        Schema::dropIfExists('eclip_documents');
        Schema::dropIfExists('eclip_document_requirement_histories');
        Schema::dropIfExists('eclip_document_requirements');
    }
};
