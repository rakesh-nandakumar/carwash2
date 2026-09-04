<?php

namespace App\Support;

/**
 * The three outcomes of resolving a tenant identity (see TenantHostResolver):
 *
 *   - central:  master control — the reserved central path prefix. No tenant
 *               is resolved.
 *   - tenant:   a concrete tenant, named by its URL prefix slug.
 *   - unknown:  an identity that must not be served at all.
 */
final class HostContext
{
    private function __construct(
        private readonly ?string $slug,
        private readonly bool $central,
        private readonly bool $unknown,
    ) {}

    public static function central(): self
    {
        return new self(null, true, false);
    }

    public static function tenant(string $slug): self
    {
        return new self($slug, false, false);
    }

    public static function unknown(): self
    {
        return new self(null, false, true);
    }

    public function isCentral(): bool
    {
        return $this->central;
    }

    public function isUnknown(): bool
    {
        return $this->unknown;
    }

    public function isTenant(): bool
    {
        return ! $this->central && ! $this->unknown;
    }

    public function slug(): string
    {
        return $this->slug ?? '';
    }
}
