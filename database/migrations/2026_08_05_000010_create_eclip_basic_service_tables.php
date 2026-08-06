<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_basic_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->string('service_type', 80)->index();
            $table->foreignId('gov_agency_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->date('referral_date')->nullable();
            $table->date('target_completion_date')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['eclip_case_id', 'service_type'], 'eclip_basic_services_case_type_unique');
        });

        Schema::create('eclip_basic_service_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basic_service_id')->constrained('eclip_basic_services')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action', 30);
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('eclip_basic_service_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basic_service_id')->constrained('eclip_basic_services')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('sha256', 64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['basic_service_id', 'version_number'], 'eclip_basic_service_docs_version_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_basic_service_documents');
        Schema::dropIfExists('eclip_basic_service_histories');
        Schema::dropIfExists('eclip_basic_services');
    }
};
