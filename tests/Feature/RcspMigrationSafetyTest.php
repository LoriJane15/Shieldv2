<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class RcspMigrationSafetyTest extends TestCase
{
    private string $originalConnection;

    private string $databaseFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalConnection = DB::getDefaultConnection();
        $this->databaseFile = tempnam(sys_get_temp_dir(), 'rcsp-migration-');
        config(['database.connections.rcsp_migration_safety' => [
            'driver' => 'sqlite', 'database' => $this->databaseFile, 'prefix' => '',
            'foreign_key_constraints' => true, 'busy_timeout' => 5000,
        ]]);
        DB::setDefaultConnection('rcsp_migration_safety');
        DB::purge('rcsp_migration_safety');
        $this->migrateBaseSchema();
    }

    protected function tearDown(): void
    {
        DB::disconnect('rcsp_migration_safety');
        DB::setDefaultConnection($this->originalConnection);
        if (is_file($this->databaseFile)) {
            unlink($this->databaseFile);
        }
        parent::tearDown();
    }

    public function test_sqlite_forward_rollback_forward_preserves_original_schema_rows_and_integrity(): void
    {
        $this->insertRepresentativeRows();
        $before = $this->originalSnapshot();
        $migration = $this->migration();

        $migration->up();
        $this->assertSame($before, $this->originalSnapshot());
        $this->assertTrue(Schema::hasColumns('rcsp_forms', ['reviewed_by_user_id', 'reviewed_at']));
        $this->assertTrue(Schema::hasColumn('rcsp_phases', 'catalog_key'));
        $this->assertTrue(Schema::hasColumn('rcsp_barangays', 'catalog_key'));
        $this->assertDatabaseObjectExists('index', 'rcsp_phases_catalog_number_unique');
        $this->assertDatabaseObjectExists('index', 'rcsp_barangays_barangay_unique');
        $this->assertDatabaseObjectExists('trigger', 'rcsp_hardening_reviewer_insert_guard');
        $this->assertSame(0, DB::table('sqlite_master')->where('name', 'like', '__temp__%')->count());

        $migration->down();
        $this->assertSame($before, $this->originalSnapshot());
        $this->assertFalse(Schema::hasColumn('rcsp_forms', 'reviewed_by_user_id'));
        $this->assertDatabaseObjectMissing('trigger', 'rcsp_hardening_reviewer_insert_guard');

        $this->migration()->up();
        $this->assertSame($before, $this->originalSnapshot());

        DB::table('users')->insert(['id' => 2, 'username' => 'DEMO-reviewer', 'name' => 'DEMO Reviewer',
            'password' => 'not-a-real-login-hash', 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('rcsp_forms')->where('id', 1)->update(['reviewed_by_user_id' => 2, 'reviewed_at' => now()]);
        $this->assertSame(2, DB::table('rcsp_forms')->where('id', 1)->value('reviewed_by_user_id'));
        DB::table('users')->where('id', 2)->delete();
        $this->assertNull(DB::table('rcsp_forms')->where('id', 1)->value('reviewed_by_user_id'));

        $this->expectException(QueryException::class);
        DB::table('rcsp_forms')->where('id', 1)->update(['reviewed_by_user_id' => 999999]);
    }

    public function test_duplicate_barangay_guard_fails_before_any_schema_change(): void
    {
        $this->insertRepresentativeRows();
        DB::table('rcsp_barangays')->insert(['id' => 2, 'barangay_id' => 1, 'municipality_id' => 1,
            'status' => 'Pending', 'current_phase' => 0, 'created_at' => now(), 'updated_at' => now()]);

        try {
            $this->migration()->up();
            $this->fail('Migration accepted duplicate RCSP barangay records.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('duplicate', strtolower($exception->getMessage()));
        }
        $this->assertFalse(Schema::hasColumn('rcsp_phases', 'catalog_key'));
        $this->assertFalse(Schema::hasColumn('rcsp_forms', 'reviewed_by_user_id'));
        $this->assertCount(2, DB::table('rcsp_barangays')->get());
    }

    public function test_rollback_refuses_before_partial_change_when_new_fields_are_used(): void
    {
        $this->insertRepresentativeRows();
        $migration = $this->migration();
        $migration->up();
        DB::table('rcsp_phases')->where('id', 1)->update(['catalog_key' => 'rcsp-demo-v1']);
        $columns = Schema::getColumnListing('rcsp_phases');

        try {
            $migration->down();
            $this->fail('Rollback accepted populated catalog data.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('catalog', strtolower($exception->getMessage()));
        }
        $this->assertSame($columns, Schema::getColumnListing('rcsp_phases'));
        $this->assertSame('rcsp-demo-v1', DB::table('rcsp_phases')->where('id', 1)->value('catalog_key'));
        $this->assertDatabaseObjectExists('index', 'rcsp_phases_catalog_number_unique');
        $this->assertDatabaseObjectExists('trigger', 'rcsp_hardening_reviewer_insert_guard');

        DB::table('rcsp_phases')->where('id', 1)->update(['catalog_key' => null]);
        DB::table('users')->insert(['id' => 2, 'username' => 'DEMO-reviewer-rollback', 'name' => 'DEMO Reviewer',
            'password' => 'not-a-real-login-hash', 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('rcsp_forms')->where('id', 1)->update(['reviewed_by_user_id' => 2]);
        try {
            $migration->down();
            $this->fail('Rollback accepted populated reviewer ownership.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('reviewer ownership', strtolower($exception->getMessage()));
        }
        $this->assertTrue(Schema::hasColumn('rcsp_forms', 'reviewed_by_user_id'));

        DB::table('rcsp_forms')->where('id', 1)->update(['reviewed_by_user_id' => null, 'reviewed_at' => now()]);
        try {
            $migration->down();
            $this->fail('Rollback accepted a populated review timestamp.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('timestamp', strtolower($exception->getMessage()));
        }
        $this->assertTrue(Schema::hasColumn('rcsp_forms', 'reviewed_at'));
        $this->assertDatabaseObjectExists('trigger', 'rcsp_hardening_reviewer_insert_guard');
    }

    private function migrateBaseSchema(): void
    {
        foreach (['0001_01_01_000000_create_users_table.php', '2025_01_01_000010_create_location_tables.php',
            '2025_01_01_000040_create_rcsp_tables.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }

    private function insertRepresentativeRows(): void
    {
        $time = '2026-01-02 03:04:05';
        DB::table('users')->insert(['id' => 1, 'username' => 'DEMO-lgu', 'name' => 'DEMO LGU',
            'password' => 'not-a-real-login-hash', 'role' => 'lgu', 'municipality_id' => 1,
            'created_at' => $time, 'updated_at' => $time]);
        DB::table('municipalities')->insert(['id' => 1, 'name' => 'DEMO Migration Municipality', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('barangays')->insert(['id' => 1, 'municipality_id' => 1, 'name' => 'DEMO Migration Barangay', 'created_at' => $time, 'updated_at' => $time]);
        foreach (range(0, 5) as $number) {
            DB::table('rcsp_phases')->insert(['id' => $number + 1, 'name' => "DEMO Migration Phase {$number}",
                'number' => $number, 'created_at' => $time, 'updated_at' => $time]);
        }
        DB::table('rcsp_activities')->insert(['id' => 1, 'rcsp_phase_id' => 1,
            'description' => 'DEMO: Migration activity', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('rcsp_barangays')->insert(['id' => 1, 'barangay_id' => 1, 'municipality_id' => 1,
            'status' => 'Ongoing', 'current_phase' => 0, 'created_at' => $time, 'updated_at' => $time]);
        DB::table('rcsp_phase_statuses')->insert(['id' => 1, 'rcsp_barangay_id' => 1,
            'phase0_completed' => 0, 'phase1_completed' => 0, 'phase2_completed' => 0,
            'phase3_completed' => 0, 'phase4_completed' => 0, 'phase5_completed' => 0,
            'created_at' => $time, 'updated_at' => $time]);
        DB::table('rcsp_forms')->insert(['id' => 1, 'lgu_user_id' => 1, 'rcsp_barangay_id' => 1,
            'rcsp_phase_id' => 1, 'rcsp_activity_id' => 1, 'conduct' => 'yes',
            'file' => 'rcsp/1/DEMO-evidence.pdf', 'status' => 'to be complied',
            'remarks' => 'DEMO migration remark', 'created_at' => $time, 'updated_at' => $time]);
        DB::table('rcsp_file_comments')->insert(['id' => 1, 'rcsp_form_id' => 1, 'rcsp_phase_id' => 1,
            'rcsp_activity_id' => 1, 'user_id' => 1, 'text' => 'DEMO migration comment',
            'created_at' => $time, 'updated_at' => $time]);
    }

    private function originalSnapshot(): array
    {
        $columns = [
            'rcsp_phases' => ['id', 'name', 'number', 'created_at', 'updated_at'],
            'rcsp_barangays' => ['id', 'barangay_id', 'municipality_id', 'status', 'current_phase', 'created_at', 'updated_at'],
            'rcsp_forms' => ['id', 'lgu_user_id', 'rcsp_barangay_id', 'rcsp_phase_id', 'rcsp_activity_id', 'conduct', 'file', 'status', 'remarks', 'created_at', 'updated_at'],
            'rcsp_phase_statuses' => ['*'], 'rcsp_file_comments' => ['*'],
        ];
        $snapshot = [];
        foreach ($columns as $table => $selection) {
            $snapshot['rows'][$table] = DB::table($table)->select($selection)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
            $snapshot['columns'][$table] = collect(DB::select("PRAGMA table_info('{$table}')"))
                ->reject(fn ($column) => in_array($column->name, ['catalog_key', 'reviewed_by_user_id', 'reviewed_at'], true))->values()->all();
            $snapshot['indexes'][$table] = collect(DB::select("PRAGMA index_list('{$table}')"))
                ->reject(fn ($index) => in_array($index->name, ['rcsp_phases_catalog_number_unique', 'rcsp_barangays_barangay_unique'], true))->values()->all();
            $snapshot['foreign_keys'][$table] = DB::select("PRAGMA foreign_key_list('{$table}')");
        }

        return json_decode(json_encode($snapshot), true, flags: JSON_THROW_ON_ERROR);
    }

    private function migration(): object
    {
        return require database_path('migrations/2026_09_08_000001_harden_rcsp_workflow.php');
    }

    private function assertDatabaseObjectExists(string $type, string $name): void
    {
        $this->assertSame(1, DB::table('sqlite_master')->where('type', $type)->where('name', $name)->count());
    }

    private function assertDatabaseObjectMissing(string $type, string $name): void
    {
        $this->assertSame(0, DB::table('sqlite_master')->where('type', $type)->where('name', $name)->count());
    }
}
