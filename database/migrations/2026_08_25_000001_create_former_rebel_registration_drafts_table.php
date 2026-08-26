<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('former_rebel_registration_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('payload');
            $table->timestamp('saved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('former_rebel_registration_drafts');
    }
};
