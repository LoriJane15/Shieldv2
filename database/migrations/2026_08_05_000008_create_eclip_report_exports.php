<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eclip_report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('municipality_id')->nullable()->constrained()->nullOnDelete();
            $table->string('format', 20);
            $table->boolean('financial_included');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('exported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_report_exports');
    }
};
