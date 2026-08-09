<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gov_agencies', function (Blueprint $table) {
            $table->string('logo_original_name')->nullable()->after('profile');
            $table->string('logo_mime_type', 100)->nullable()->after('logo_original_name');
            $table->unsignedBigInteger('logo_size_bytes')->nullable()->after('logo_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('gov_agencies', function (Blueprint $table) {
            $table->dropColumn(['logo_original_name', 'logo_mime_type', 'logo_size_bytes']);
        });
    }
};
