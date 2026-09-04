<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\CurrentContext;
use App\Services\TenantHostResolver;
use App\Support\TenantReachability;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Resolves which tenant this request belongs to and records it on
 * CurrentContext before anything else runs. Identity comes from the URL path
 * prefix (/{slug}/...) — the server-rendered Blade equivalent of the SPA's
 * X-Tenant-Slug header; the host fallback lives in TenantHostResolver::resolve()
 * but is switched off. The reserved central prefix is "master control" — no
 * tenant is resolved there, and central-guard routes take over.
 *
 * Fails closed: every rejection — unknown slug, suspended, cancelled or
 * expired-trial tenant — is the same 404, so no probe can tell tenants apart
 * or confirm a slug exists.
 */
class IdentifyTenant
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly TenantHostResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        // CurrentContext is a container singleton — start every request with
        // a clean slate rather than risk a previous request's tenant leaking
        // forward (see CurrentContext::resetTenant()'s doc block).
        $this->context->resetTenant();

        // Middleware runs for EVERY web-group request — including the central
        // panel's own paths. Those have no `{tenant}` segment at all: mark
        // central and let EnsureCentralContext + auth:central take over.
        if (str_starts_with((string) $request->route()?->getName(), 'central.')
            || $request->path() === '/') {
            $this->context->markCentral();

            return $next($request);
        }

        $hostContext = $this->resolver->resolveFromPath($request->route('tenant'));

        if (! $hostContext->isTenant()) {
            throw new NotFoundHttpException('Unknown tenant.');
        }

        $tenant = Tenant::query()->where('slug', $hostContext->slug())->first();

        if (! $tenant || ! TenantReachability::check($tenant)) {
            throw new NotFoundHttpException('Unknown tenant.');
        }

        // Resolve BEFORE loading the session user: TenantScope consults
        // CurrentContext::tenantId(), which falls back to Auth::user() when no
        // tenant override is set — loading that user runs a scoped query, whose
        // scope would then try to load the user again, forever (a re-entrant
        // Auth::user() that recurses until memory is exhausted).
        $this->context->setTenant($tenant->id);

        // Every route('...') / redirect()->route(...) generated for the rest
        // of this request — and any view it renders — must carry the tenant
        // slug prefix. Route names never change; the prefix is injected as the
        // default value of the `{tenant}` route parameter.
        URL::defaults(['tenant' => $tenant->slug]);

        // The {tenant} route parameter must NOT bind into controller action
        // arguments — the ~80 existing action signatures (show(Job $job)) must
        // stay untouched. Route parameters bind when the route RUNS, which is
        // after middleware, so forgetting it here is safe. Pinned by a test.
        $request->route()->forgetParameter('tenant');

        // The tenant's configured timezone drives every timestamp this request
        // renders — applied as early as possible, like the rest of the tenant's
        // identity (see CurrentContext::timezone()).
        $this->applyTenantTimezone();

        // A session whose own tenant disagrees with the resolved tenant is
        // rejected outright — that would otherwise be a live cross-tenant
        // session hijack (a stale cookie replayed against another tenant's
        // prefix). The guard's user is looked up scoped to the resolved
        // tenant, so a foreign session loads as null and is caught here.
        // Skipped entirely when the request carries no session (non-stateful
        // clients): there is no session to replay.
        if ($request->hasSession()) {
            $guard = Auth::guard('web');
            $sessionUserId = $request->session()->get($guard->getName());
            if ($sessionUserId !== null) {
                $user = $guard->user();
                if (! $user || $user->tenant_id !== $tenant->id) {
                    // Log the tenant identity out only. One origin now hosts
                    // both panels, so both guards' state lives in the same
                    // session store — a full invalidate() would also destroy
                    // a central admin's logged-in session (e.g. after an
                    // impersonation hand-off), dropping the operator from
                    // /admin mid-flow.
                    $guard->logout();

                    return redirect()->route('login')
                        ->withErrors(['email' => 'Please sign in to this workspace.']);
                }
            }
        }

        return $next($request);
    }

    private function applyTenantTimezone(): void
    {
        $timezone = $this->context->timezone();

        if ($timezone !== config('app.timezone')) {
            date_default_timezone_set($timezone);
        }
    }
}
