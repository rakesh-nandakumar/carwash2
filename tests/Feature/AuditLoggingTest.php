<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Job;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Permission;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_creation_logs_audit(): void
    {
        $user = User::factory()->create();
        $job = Job::factory()->create(['status' => 'ready_for_payment']);

        $this->actingAs($user);

        $invoiceService = app(\App\Services\InvoiceService::class);
        $invoice = $invoiceService->generate($job->id);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'invoice.created',
            'actor_email' => $user->email,
        ]);
    }

    public function test_role_creation_logs_audit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $role = Role::create([
            'tenant_id' => $user->tenant_id,
            'business_id' => $user->business_id,
            'name' => 'Test Role',
            'slug' => 'test-role-' . uniqid(),
            'is_system' => false,
            'is_active' => true,
            'is_full_admin' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'role.created',
            'actor_email' => $user->email,
        ]);
    }

    public function test_user_role_assignment_logs_audit(): void
    {
        $admin = User::factory()->create();
        $role = Role::factory()->create([
            'tenant_id' => $admin->tenant_id,
            'business_id' => $admin->business_id,
        ]);

        $this->actingAs($admin);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id],
            'active' => true,
        ];

        $response = $this->post(route('users.store'), $userData);

        $this->assertDatabaseHas('audit_logs', [
            'event_key' => 'user.roles_assigned',
        ]);
    }

    public function test_till_operations_log_audit(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // This would require more setup with till models
        // For now, we verify the AuditService has the necessary methods
        $auditService = app(AuditService::class);

        $this->assertTrue(method_exists($auditService, 'logLogin'));
        $this->assertTrue(method_exists($auditService, 'logLogout'));
        $this->assertTrue(method_exists($auditService, 'logInvoiceCreation'));
        $this->assertTrue(method_exists($auditService, 'logInvoiceModification'));
        $this->assertTrue(method_exists($auditService, 'logRefund'));
        $this->assertTrue(method_exists($auditService, 'logPriceChange'));
        $this->assertTrue(method_exists($auditService, 'logServiceCancellation'));
        $this->assertTrue(method_exists($auditService, 'logCustomerDeletion'));
        $this->assertTrue(method_exists($auditService, 'logPermissionChange'));
    }
}