<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_assistance_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assistance_request_id')->constrained('eclip_assistance_requests')->restrictOnDelete();
            $table->foreignId('assistance_revision_id')->constrained('eclip_assistance_revisions')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('release_reference', 150)->unique();
            $table->date('released_at');
            $table->text('remarks')->nullable();
            $table->string('acknowledgment_path');
            $table->string('acknowledgment_original_name');
            $table->string('acknowledgment_mime_type', 120);
            $table->unsignedBigInteger('acknowledgment_size_bytes');
            $table->string('acknowledgment_sha256', 64);
            $table->foreignId('released_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['eclip_case_id', 'released_at'], 'eclip_release_case_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_assistance_releases');
    }
};
