<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    /**
     * Databases RefreshDatabase is allowed to wipe. Anything else — notably the
     * working `shield_db` holding the imported legacy dataset — is refused.
     *
     * Running the suite with DB_CONNECTION=mysql (to exercise the Smoke tests
     * against real data) otherwise lets RefreshDatabase run migrate:fresh on it
     * and destroy the import.
     */
    private const WIPEABLE = [':memory:', 'shield_testing', 'testing'];

    protected function setUp(): void
    {
        // Must run BEFORE parent::setUp() — RefreshDatabase wipes from inside it.
        if ($this->usesRefreshDatabase()) {
            // The container is not booted yet, so read the environment directly.
            // phpunit.xml pins the default suite to sqlite :memory:.
            $name = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: ':memory:';

            if (! in_array($name, self::WIPEABLE, true)) {
                $this->markTestSkipped(
                    "Skipped: RefreshDatabase would wipe '{$name}'. These tests build their own "
                    .'fixtures, so run them on the default sqlite suite (php artisan test).'
                );
            }
        }

        parent::setUp();
    }

    private function usesRefreshDatabase(): bool
    {
        foreach (class_uses_recursive(static::class) as $trait) {
            if (str_contains($trait, 'RefreshDatabase') || str_contains($trait, 'DatabaseMigrations')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Smoke tests run against the real, imported legacy dataset rather than a
     * factory-built one. Skip them on the default in-memory sqlite suite.
     */
    protected function skipUnlessLegacyDataPresent(): void
    {
        if (! Schema::hasTable('users') || User::count() === 0) {
            $this->markTestSkipped('Needs the imported legacy dataset — see README (php artisan import:legacy).');
        }
    }
}
