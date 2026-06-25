<?php

namespace App\Jobs\Concerns;

use App\Models\Landlord\Studio;

trait RunsAcrossAllTenants
{
    /**
     * Run the given callback once for every active, provisioned studio,
     * with the tenant connection switched to that studio's database for
     * the duration of the call. Always restores to "no current tenant"
     * afterward, even if the callback throws.
     */
    public function forEachTenant(callable $callback): void
    {
        Studio::where('status', 'active')
            ->whereNotNull('database')
            ->each(function (Studio $studio) use ($callback) {
                $studio->makeCurrent();

                try {
                    $callback($studio);
                } finally {
                    Studio::forgetCurrent();
                }
            });
    }
}