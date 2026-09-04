<?php

namespace App\Services;

use App\Models\CentralAdmin;
use App\Models\ImpersonationToken;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Lets a platform operator land on a tenant's own URL prefix already logged
 * in as one of its users, without ever handling that user's password. A minted
 * token is a plaintext, single-use, short-lived credential — only its hash is
 * stored (same treatment as a password reset token) so a leaked audit log or
 * database dump can't be replayed.
 */
class Impersonation
{
    private const TTL_SECONDS = 90;

    public function startFor(Tenant $tenant, CentralAdmin $centralAdmin, ?User $user = null): array
    {
        $user ??= User::query()
            ->withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereHas('roles', fn ($qq) => $qq->withoutTenantScope()->where('slug', 'super_admin'))
                    ->orWhereIn('role', ['super_admin', 'owner']);
            })
            ->orderBy('id')
            ->first();

        // Provisioning always creates a super_admin (see TenantProvisioning),
        // so this only happens if it was since deleted or stripped.
        if (! $user) {
            abort(422, 'This tenant has no administrator to impersonate.');
        }

        $plainToken = Str::random(64);

        ImpersonationToken::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'central_admin_id' => $centralAdmin->id,
            'token_hash' => hash('sha256', $plainToken),
            // UTC, not now(): IdentifyTenant sets the process default timezone
            // to the CONSUMING request's tenant (see applyTenantTimezone()),
            // which can differ from the CENTRAL (UTC) context this is minted
            // in. Comparing two now()s taken under different default timezones
            // against the same naive DB column made every token in a
            // non-UTC tenant look expired on arrival.
            'expires_at' => now('UTC')->addSeconds(self::TTL_SECONDS),
        ]);

        return ['url' => $this->landingUrl($tenant, $plainToken), 'user' => $user];
    }

    /**
     * Where the operator's browser is sent to redeem the token: always the
     * tenant's own URL prefix — /{slug}/impersonate/{token} — which is what
     * binds the resulting session to the right tenant; the token is refused
     * anywhere else.
     */
    private function landingUrl(Tenant $tenant, string $plainToken): string
    {
        return url("/{$tenant->slug}/impersonate/{$plainToken}");
    }

    /**
     * Consumes a token for the CURRENT tenant (as resolved by IdentifyTenant
     * from the request's own URL prefix) — a token minted for one tenant can
     * never be replayed against another prefix, even if somehow obtained.
     */
    public function consume(string $plainToken, int $resolvedTenantId): ?User
    {
        $token = ImpersonationToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('tenant_id', $resolvedTenantId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now('UTC'))
            ->first();

        if (! $token) {
            return null;
        }

        $token->update(['used_at' => now('UTC')]);

        return User::query()->withoutTenantScope()->find($token->user_id);
    }
}
