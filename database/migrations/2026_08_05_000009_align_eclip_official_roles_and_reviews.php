<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role_migrated_from', 50)->nullable()->after('role');
        });
        foreach ([
            'eclip_assessor' => 'lswdo',
            'dilg_reviewer' => 'dilg_provincial_focal',
            'eclip_funding_officer' => 'dilg_fms',
        ] as $oldRole => $officialRole) {
            DB::table('users')->where('role', $oldRole)->update([
                'role_migrated_from' => $oldRole,
                'role' => $officialRole,
            ]);
        }

        Schema::table('eclip_dilg_reviews', function (Blueprint $table) {
            $table->string('review_level', 30)->nullable()->after('reviewed_by')->index();
        });
        DB::table('eclip_dilg_reviews')->where('decision', 'approved')->update(['review_level' => 'national']);
        DB::table('eclip_dilg_reviews')->whereNull('review_level')->update(['review_level' => 'provincial']);
    }

    public function down(): void
    {
        Schema::table('eclip_dilg_reviews', function (Blueprint $table) {
            $table->dropIndex(['review_level']);
            $table->dropColumn('review_level');
        });
        DB::table('users')->whereNotNull('role_migrated_from')->update([
            'role' => DB::raw('role_migrated_from'),
        ]);
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role_migrated_from');
        });
    }
};
