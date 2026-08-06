<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_fund_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eclip_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assistance_request_id')->constrained('eclip_assistance_requests')->restrictOnDelete();
            $table->foreignId('assistance_revision_id')->constrained('eclip_assistance_revisions')->restrictOnDelete();
            $table->string('type', 20)->index();
            $table->decimal('amount', 15, 2);
            $table->string('reference_number', 150);
            $table->date('transaction_date');
            $table->text('remarks')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('proof_original_name')->nullable();
            $table->string('proof_mime_type', 120)->nullable();
            $table->unsignedBigInteger('proof_size_bytes')->nullable();
            $table->string('proof_sha256', 64)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['type', 'reference_number'], 'eclip_fund_type_reference_unique');
            $table->index(['eclip_case_id', 'type'], 'eclip_fund_case_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_fund_transactions');
    }
};
