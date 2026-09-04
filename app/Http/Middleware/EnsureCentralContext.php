<?php

namespace App\Http\Middleware;

use App\Services\CurrentContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Belt-and-braces alongside `auth:central`: rejects any /admin/* request
 * that IdentifyTenant did not mark central — the master control panel must
 * never be reachable "as" a specific tenant's context.
 *
 * Checks isCentral() rather than `tenantId() !== null`: tenantId() falls back
 * to the `web` guard's session user when no tenant override is set, and that
 * guard's session outlives an impersonation hand-off (login() + regenerate()
 * rotates the session id but keeps its data — see
 * Auth\ImpersonationSessionController). A central admin returning to /admin
 * after impersonating a tenant would otherwise still carry that tenant user
 * in the `web` guard and be 404'd here despite never having left central
 * context. isCentral() is the flag IdentifyTenant sets directly for exactly
 * this request, so it isn't subject to that fallback.
 */
class EnsureCentralContext
{
    public function __construct(private readonly CurrentContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->context->isCentral()) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
