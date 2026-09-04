<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CurrentContext;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    public function test_tenant_dashboard_renders(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme Car Wash',
            'slug' => 'acme',
            'status' => 'active',
        ]);

        $user = app(CurrentContext::class)->runForTenant($tenant->id, function () use ($tenant) {
            $business = Business::create([
                'name' => 'Acme Main',
                'code' => 'ACME-1',
            ]);

            return User::create([
                'business_id' => $business->id,
                'name' => 'Owner',
                'email' => 'owner@acme.test',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'active' => true,
            ]);
        });

        $this->actingAs($user)->get('/acme/dashboard')->assertOk();
    }
}
