<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ib39_cdr_photos')) {
            return;
        }
        DB::unprepared('DROP TRIGGER IF EXISTS ib39_cdr_photo_type_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS ib39_cdr_photo_type_update');
        DB::unprepared("CREATE TRIGGER ib39_cdr_photo_type_insert BEFORE INSERT ON ib39_cdr_photos WHEN NEW.photo_type <> 'fr_photo' BEGIN SELECT RAISE(ABORT, 'Invalid CDR photo type'); END");
        DB::unprepared("CREATE TRIGGER ib39_cdr_photo_type_update BEFORE UPDATE OF photo_type ON ib39_cdr_photos WHEN NEW.photo_type <> 'fr_photo' BEGIN SELECT RAISE(ABORT, 'Invalid CDR photo type'); END");
    }

    public function down(): void
    {
        if (! Schema::hasTable('ib39_cdr_photos')) {
            return;
        }
        DB::unprepared('DROP TRIGGER IF EXISTS ib39_cdr_photo_type_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS ib39_cdr_photo_type_update');
        DB::unprepared("CREATE TRIGGER ib39_cdr_photo_type_insert BEFORE INSERT ON ib39_cdr_photos WHEN NEW.photo_type NOT IN ('whole_body_with_firearm','half_body_without_firearm') BEGIN SELECT RAISE(ABORT, 'Invalid CDR photo type'); END");
        DB::unprepared("CREATE TRIGGER ib39_cdr_photo_type_update BEFORE UPDATE OF photo_type ON ib39_cdr_photos WHEN NEW.photo_type NOT IN ('whole_body_with_firearm','half_body_without_firearm') BEGIN SELECT RAISE(ABORT, 'Invalid CDR photo type'); END");
    }
};
