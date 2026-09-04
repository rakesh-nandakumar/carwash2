<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Rules\ReservedSlug;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    public function test_unknown_slug_suspended_cancelled_and_expired_all_return_identical_404(): void
    {
        Tenant::create(['name' => 'Frozen', 'slug' => 'frozen', 'status' => 'suspended']);
        Tenant::create(['name' => 'Dead', 'slug' => 'dead', 'status' => 'cancelled']);
        Tenant::create(['name' => 'Old Trial', 'slug' => 'oldtrial', 'status' => 'trial', 'trial_ends_at' => now()->subDay()]);

        $frozen = $this->get('/frozen/dashboard');
        $dead = $this->get('/dead/dashboard');
        $old = $this->get('/oldtrial/dashboard');
        $missing = $this->get('/nonsense/dashboard');

        foreach ([$frozen, $dead, $old, $missing] as $i => $response) {
            fwrite(STDERR, 'R'.$i.'='.$response->getStatusCode().PHP_EOL);
            $response->assertNotFound();
        }

        $this->assertSame($missing->getStatusCode(), $frozen->getStatusCode());
    }

    public function test_reserved_slug_is_rejected(): void
    {
        $rule = new ReservedSlug;

        foreach (['admin', 'central', 'uploads', 'login', 'css', 'up'] as $reserved) {
            $fail = fn () => throw ValidationException::withMessages(['slug' => 'That slug is reserved.']);
            $this->expectException(ValidationException::class);
            $rule->validate('slug', $reserved, $fail);
        }
    }

    public function test_reachable_tenant_resolves(): void
    {
        Tenant::create(['name' => 'Live One', 'slug' => 'liveone', 'status' => 'active']);

        $this->get('/liveone/login')->assertOk();
        $this->get('/liveone')->assertRedirect(route('login'));
    }
}
