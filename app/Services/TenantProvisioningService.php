<?php

namespace App\Services;

use App\Models\Landlord\Studio;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantProvisioningService
{
    /**
     * Build a safe physical database name from a studio's slug,
     * e.g. "neural-studios" -> "db_neural_studios".
     */
    public function generateDatabaseName(string $slug): string
    {
        return 'db_' . Str::slug($slug, '_');
    }

    /**
     * Create the physical tenant database for a studio (if it doesn't
     * already have one) and run tenant migrations against it.
     */
    public function provision(Studio $studio): void
    {
        if (! $studio->database) {
            $studio->update([
                'database' => $this->generateDatabaseName($studio->slug),
            ]);
        }

        $this->createDatabase($studio->database);
        $this->migrateTenantDatabase($studio);
    }

    protected function createDatabase(string $databaseName): void
    {
        // generateDatabaseName() only ever produces a safe slug, but this
        // guards against the column ever being set another way.
        if (! preg_match('/^[a-z0-9_]+$/', $databaseName)) {
            throw new \InvalidArgumentException("Invalid tenant database name: {$databaseName}");
        }

        // Always run this on the landlord connection — it's already
        // connected to a real database, so it can issue server-level DDL
        // without needing the (not-yet-existing) tenant DB selected.
        DB::connection('landlord')->statement(
            "CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }

    protected function migrateTenantDatabase(Studio $studio): void
    {
        $studio->makeCurrent();

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        Studio::forgetCurrent();
    }
}