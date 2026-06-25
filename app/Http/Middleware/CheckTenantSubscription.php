<?php

namespace App\Http\Middleware;

use App\Models\Landlord\Studio;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSubscription
{
    /** Always reachable, regardless of billing status. */
    private const ALWAYS_ALLOWED = [
        'admin.login', 'admin.logout',
        'subscription.pay', 'subscription.status',
    ];

    /** Admin routes still reachable while suspended — read + export only. */
    private const READ_ONLY_ALLOWED = [
        'admin.dashboard',
        'admin.bookings.index', 'admin.bookings.show',
        'admin.clients.index', 'admin.clients.show',
        'admin.calendar.index',
        'admin.finance.index',
        'admin.export.*',
    ];

    /** Admin routes still reachable while frozen — calendar + export only. */
    private const FROZEN_ALLOWED = [
        'admin.dashboard', 'admin.calendar.index',
        'admin.export.*', 'subscription.pay',
    ];

    /**
     * Client-facing routes ALWAYS bypass billing status entirely.
     * A client who already paid deserves their delivery regardless of
     * the studio's billing dispute with us.
     */
    private const CLIENT_PORTAL_BYPASS = [
        'portal.*', 'selection.*', 'gallery.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $studio = Studio::current();

        // No tenant resolved (shouldn't happen once NeedsTenant runs first,
        // but fail open rather than crash on a null status).
        if (! $studio) {
            return $next($request);
        }

        $status = $studio->subscription_status;

        if (in_array($status, ['active', 'trialing'], true)) {
            return $next($request);
        }

        if ($this->matchesPatterns($request, self::CLIENT_PORTAL_BYPASS)) {
            return $next($request);
        }

        if ($this->matchesPatterns($request, self::ALWAYS_ALLOWED)) {
            return $next($request);
        }

        return match ($status) {
            // Grace = warning only (handled by the dunning sequence in
            // Phase 3), full access stays intact.
            'grace' => $next($request),
            'suspended' => $this->handleSuspended($request, $next, $studio),
            'frozen' => $this->handleFrozen($request, $next, $studio),
            'archived' => response()->view('admin.account-archived', ['studio' => $studio]),
            default => $next($request),
        };
    }

    protected function handleSuspended(Request $request, Closure $next, Studio $studio): Response
    {
        if ($this->isPublicRoute($request)) {
            return response()->view('public.booking-suspended', ['studio' => $studio]);
        }

        if ($this->isAdminRoute($request) && ! $this->matchesPatterns($request, self::READ_ONLY_ALLOWED)) {
            return redirect()->route('admin.subscription.status')
                ->with('warning', 'Account restricted. Restore access to continue.');
        }

        view()->share('suspension_banner', true);
        view()->share('days_overdue', $studio->payment_overdue_days);

        return $next($request);
    }

    protected function handleFrozen(Request $request, Closure $next, Studio $studio): Response
    {
        if ($this->isPublicRoute($request)) {
            return response()->view('public.booking-suspended', ['studio' => $studio]);
        }

        if (! $this->matchesPatterns($request, self::FROZEN_ALLOWED)) {
            return redirect()->route('admin.subscription.status');
        }

        view()->share('suspension_banner', true);

        return $next($request);
    }

    protected function matchesPatterns(Request $request, array $patterns): bool
    {
        $routeName = $request->route()?->getName();

        if (! $routeName) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    protected function isPublicRoute(Request $request): bool
    {
        return ! str_starts_with((string) $request->route()?->getName(), 'admin.');
    }

    protected function isAdminRoute(Request $request): bool
    {
        return str_starts_with((string) $request->route()?->getName(), 'admin.');
    }
}