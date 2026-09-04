<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RouteSlugTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenancy();
    }

    public function test_route_generator_emits_slug_prefix_within_tenant_request(): void
    {
        $this->actingAs($this->ownerA)->get('/alpha/dashboard')->assertOk();

        $this->assertSame(url('/alpha/jobs'), route('jobs.index'));
        $this->assertSame(url('/alpha/customers'), route('customers.index'));
        $this->assertSame(url('/alpha/invoices'), route('invoices.index'));
    }

    public function test_route_parameter_tenant_is_consumed_not_bound_into_controller(): void
    {
        // Matrix test proves the ~55 routes work with slugs; here we pin the
        // mechanism directly: after the request the route object carries no
        // `tenant` parameter (forgetParameter), so no controller action can
        // ever receive it.
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('dashboard');
        $request = \Illuminate\Http\Request::create('http://localhost/alpha/dashboard', 'GET');
        $request->setRouteResolver(fn () => $route);
        $route->bind($request);

        $identify = app(\App\Http\Middleware\IdentifyTenant::class);
        $response = $identify->handle($request, fn ($request) => response('ok'));

        $this->assertSame('ok', $response->getContent());
        $this->assertNull($request->route()->parameter('tenant'));

        // slug still feeds the URL generator…
        $this->assertSame(url('/alpha/jobs'), route('jobs.index'));
    }

    public function test_controller_actions_receive_no_tenant_argument(): void
    {
        $this->actingAs($this->ownerA)->get('/alpha/jobs')->assertOk();
        $this->actingAs($this->ownerA)->get('/alpha/appointments')->assertOk();
    }
}
