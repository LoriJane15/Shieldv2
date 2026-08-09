<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('eclip_authentication_requests')) {
            Schema::create('eclip_authentication_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('eclip_case_id')->unique()->constrained()->cascadeOnDelete();
                $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
                $table->string('status', 30)->default('pending')->index();
                $table->timestamp('requested_at');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('due_at')->nullable();
                $table->timestamp('decided_at')->nullable();
                $table->string('certification_reference')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->index(['assigned_to', 'status'], 'eclip_auth_assignee_status_idx');
            });
        }

        if (! Schema::hasTable('eclip_authentication_histories')) {
            Schema::create('eclip_authentication_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('authentication_request_id')->constrained('eclip_authentication_requests')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->restrictOnDelete();
                $table->string('from_status', 30)->nullable();
                $table->string('to_status', 30);
                $table->text('remarks')->nullable();
                $table->json('data')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
                $table->index(['authentication_request_id', 'created_at'], 'eclip_auth_history_request_date_idx');
            });
        } elseif (! collect(Schema::getIndexes('eclip_authentication_histories'))
            ->contains(fn (array $index) => $index['name'] === 'eclip_auth_history_request_date_idx')) {
            Schema::table('eclip_authentication_histories', function (Blueprint $table) {
                $table->index(['authentication_request_id', 'created_at'], 'eclip_auth_history_request_date_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('eclip_authentication_histories');
        Schema::dropIfExists('eclip_authentication_requests');
    }
};
