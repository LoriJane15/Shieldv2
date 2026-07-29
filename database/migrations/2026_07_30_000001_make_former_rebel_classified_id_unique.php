<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException as MigrationRuntimeException;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicates = DB::table('former_rebels')
            ->select('classified_id')
            ->groupBy('classified_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new MigrationRuntimeException(
                'Duplicate Former Rebel classified IDs must be resolved before applying the unique constraint.'
            );
        }

        Schema::table('former_rebels', function (Blueprint $table) {
            $table->unique('classified_id', 'former_rebels_classified_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('former_rebels', function (Blueprint $table) {
            $table->dropUnique('former_rebels_classified_id_unique');
        });
    }
};
