<?php
// app/Tenancy/SessionTenancyBootstrapper.php
//
// Fixes cross-tab session pollution in multi-tenant apps.
//
// Root cause: the browser sends the same session cookie
// (e.g. trexo-erp-session) to BOTH localhost (admin) and
// hema.localhost (tenant), because the cookie has no domain
// restriction.  When the admin tab refreshes, Laravel picks up
// the tenant session (or vice-versa) and redirects to the wrong
// login page.
//
// Fix: override the session cookie name to be unique per tenant
// subdomain (e.g. "tenant-hema-session") so each subdomain
// carries its own isolated session cookie.  The central
// admin domain keeps the default cookie name untouched.

namespace App\Tenancy;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class SessionTenancyBootstrapper implements TenancyBootstrapper
{
    /** Original cookie name before tenancy booted, stored for revert. */
    protected string $originalCookie;

    /** Original session domain before tenancy booted, stored for revert. */
    protected string|null $originalDomain;

    public function bootstrap(Tenant $tenant): void
    {
        // Save originals so we can restore them after the request
        $this->originalCookie = config('session.cookie');
        $this->originalDomain = config('session.domain');

        // Use the tenant's subdomain as the unique cookie name.
        // e.g. tenant id "hema" → cookie "tenant-hema-session"
        $tenantId = $tenant->getTenantKey();

        config([
            // Unique cookie name per tenant — prevents cross-tab leakage
            'session.cookie' => 'tenant_' . $tenantId . '_v2_session',

            // Lock the cookie to the exact subdomain so it is NOT sent
            // to sibling subdomains or the root domain.
            // e.g. hema.localhost  (null lets browser default to the
            //      exact host which is what we want for localhost dev)
            'session.domain' => null,
        ]);
    }

    public function revert(): void
    {
        // Restore original values after the tenant request ends
        config([
            'session.cookie' => $this->originalCookie,
            'session.domain' => $this->originalDomain,
        ]);
    }
}
