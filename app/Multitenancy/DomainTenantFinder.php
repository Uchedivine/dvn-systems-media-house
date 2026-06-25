<?php

namespace App\Multitenancy;

use App\Models\Landlord\Studio;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class DomainTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = $request->getHost();

        // Custom domain takes priority — a studio could have both set.
        if ($studio = Studio::where('custom_domain', $host)->first()) {
            return $studio;
        }

        // Otherwise resolve by subdomain — first label of the host
        // (e.g. "neuralstudios.dvnsystems.test" -> "neuralstudios").
        $subdomain = explode('.', $host)[0];

        return Studio::where('subdomain', $subdomain)->first();
    }
}
