<?php

namespace Tests\Feature;

use App\Models\Communication;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Services\CommunicationService;
use App\Services\CurrentContext;
use Tests\TestCase;

class WhatsAppInvoiceNotificationTest extends TestCase
{
    use CreatesTenancyFixtures;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenancy();
    }

    public function test_send_invoice_to_phone_number_creates_communication_record(): void
    {
        $this->actingAs($this->ownerA);

        $job = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-WHATSAPP-001',
                    'full_name' => 'WhatsApp Test Customer',
                    'phone' => '0770000002',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'WHATSAPP-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-WHATSAPP-001',
                    'status' => 'ready_for_payment',
                ]);

                $invoice = Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-WHATSAPP-001',
                    'status' => 'issued',
                    'subtotal' => 1000,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 1000,
                    'paid' => 0,
                    'balance' => 1000,
                ]);

                return $job;
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($job) {
                // Test log-only method (no API required)
                $result = app(CommunicationService::class)->sendInvoiceViaLogOnly(
                    job: $job,
                    amount: 1000,
                    paymentMethod: 'cash'
                );

                $this->assertArrayHasKey('id', $result);
                $this->assertArrayHasKey('status', $result);

                $this->assertDatabaseHas('communications', [
                    'id' => $result['id'],
                    'customer_id' => $job->customer_id,
                    'job_id' => $job->id,
                    'channel' => 'log',
                    'direction' => 'outbound',
                    'recipient' => 'system-log',
                ]);

                $communication = Communication::find($result['id']);
                $this->assertStringContainsString('INVOICE DETAILS', $communication->message);
                $this->assertStringContainsString($job->job_number, $communication->message);
                $this->assertStringContainsString('1000', $communication->message);
            }
        );
    }

    public function test_communication_message_format(): void
    {
        $this->actingAs($this->ownerA);

        $job = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-FORMAT-001',
                    'full_name' => 'Format Test Customer',
                    'phone' => '0770000003',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'FORMAT-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-FORMAT-001',
                    'status' => 'ready_for_payment',
                ]);

                Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-FORMAT-001',
                    'status' => 'issued',
                    'subtotal' => 500,
                    'discount' => 50,
                    'tax' => 45,
                    'total' => 495,
                    'paid' => 0,
                    'balance' => 495,
                ]);

                return $job;
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($job) {
                // Test log-only method (no API required)
                $result = app(CommunicationService::class)->sendInvoiceViaLogOnly(
                    job: $job,
                    amount: 500,
                    paymentMethod: 'card'
                );

                $communication = Communication::find($result['id']);

                // Verify message structure
                $this->assertStringContainsString('🧾 *INVOICE DETAILS*', $communication->message);
                $this->assertStringContainsString('*Job Number:*', $communication->message);
                $this->assertStringContainsString('*Customer:*', $communication->message);
                $this->assertStringContainsString('*Vehicle:*', $communication->message);
                $this->assertStringContainsString('*Payment Details:*', $communication->message);
                $this->assertStringContainsString('Amount Paid:', $communication->message);
                $this->assertStringContainsString('Payment Method:', $communication->message);
                $this->assertStringContainsString('Invoice Total:', $communication->message);
                $this->assertStringContainsString('Balance:', $communication->message);

                // Verify actual data
                $this->assertStringContainsString('JOB-FORMAT-001', $communication->message);
                $this->assertStringContainsString('Format Test Customer', $communication->message);
                $this->assertStringContainsString('FORMAT-001', $communication->message);
                $this->assertStringContainsString('500.00', $communication->message);
                $this->assertStringContainsString('card', $communication->message);
                $this->assertStringContainsString('495.00', $communication->message);
            }
        );
    }

    public function test_email_method_creates_communication_record(): void
    {
        $this->actingAs($this->ownerA);

        $job = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-EMAIL-001',
                    'full_name' => 'Email Test Customer',
                    'phone' => '0770000004',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'EMAIL-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-EMAIL-001',
                    'status' => 'ready_for_payment',
                ]);

                Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-EMAIL-001',
                    'status' => 'issued',
                    'subtotal' => 750,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 750,
                    'paid' => 0,
                    'balance' => 750,
                ]);

                return $job;
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($job) {
                // Test email method (no WhatsApp API required)
                $result = app(CommunicationService::class)->sendInvoiceViaEmail(
                    email: 'test@example.com',
                    job: $job,
                    amount: 750,
                    paymentMethod: 'bank_transfer'
                );

                $this->assertArrayHasKey('id', $result);
                $this->assertArrayHasKey('status', $result);

                $this->assertDatabaseHas('communications', [
                    'id' => $result['id'],
                    'customer_id' => $job->customer_id,
                    'job_id' => $job->id,
                    'channel' => 'email',
                    'direction' => 'outbound',
                    'recipient' => 'test@example.com',
                ]);

                $communication = Communication::find($result['id']);
                $this->assertStringContainsString('INVOICE DETAILS', $communication->message);
                $this->assertStringContainsString($job->job_number, $communication->message);
                $this->assertStringContainsString('750', $communication->message);
            }
        );
    }

    public function test_whatsapp_web_url_generation(): void
    {
        $this->actingAs($this->ownerA);

        $job = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-WHATSAPP-URL-001',
                    'full_name' => 'WhatsApp URL Test Customer',
                    'phone' => '0770000005',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'WHATSAPP-URL-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-WHATSAPP-URL-001',
                    'status' => 'ready_for_payment',
                ]);

                Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-WHATSAPP-URL-001',
                    'status' => 'issued',
                    'subtotal' => 1200,
                    'discount' => 100,
                    'tax' => 110,
                    'total' => 1210,
                    'paid' => 0,
                    'balance' => 1210,
                ]);

                return $job;
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($job) {
                // Test WhatsApp Web URL generation (no API required)
                $whatsappUrl = app(CommunicationService::class)->generateWhatsAppWebUrl(
                    phoneNumber: '0752072772',
                    job: $job,
                    amount: 1200,
                    paymentMethod: 'cash'
                );

                // Verify URL structure
                $this->assertStringStartsWith('https://wa.me/0752072772?text=', $whatsappUrl);
                $this->assertStringContainsString('INVOICE DETAILS', $whatsappUrl);
                $this->assertStringContainsString($job->job_number, $whatsappUrl);
                $this->assertStringContainsString('1200', $whatsappUrl);
                $this->assertStringContainsString('cash', $whatsappUrl);

                // Verify URL is properly encoded
                $this->assertStringNotContainsString(' ', $whatsappUrl); // Spaces should be encoded
                $this->assertStringContainsString('%20', $whatsappUrl); // URL encoded spaces
            }
        );
    }

    public function test_whatsapp_url_shows_as_clickable_link(): void
    {
        $this->actingAs($this->ownerA);

        $job = app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () {
                $businessId = $this->ownerA->business_id;
                $branchId = $this->ownerA->branch_id;

                $customer = Customer::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_code' => 'CUS-LINK-001',
                    'full_name' => 'Link WhatsApp Test Customer',
                    'phone' => '0770000006',
                ]);

                $vehicle = Vehicle::create([
                    'customer_id' => $customer->id,
                    'registration_number' => 'LINK-001',
                ]);

                $job = Job::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'job_number' => 'JOB-LINK-001',
                    'status' => 'ready_for_payment',
                ]);

                Invoice::create([
                    'business_id' => $businessId,
                    'branch_id' => $branchId,
                    'customer_id' => $customer->id,
                    'job_id' => $job->id,
                    'invoice_number' => 'INV-LINK-001',
                    'status' => 'issued',
                    'subtotal' => 500,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => 500,
                    'paid' => 0,
                    'balance' => 500,
                ]);

                return $job;
            }
        );

        app(CurrentContext::class)->runForTenant(
            $this->tenantA->id,
            function () use ($job) {
                // Simulate payment processing
                $whatsappUrl = app(CommunicationService::class)->generateWhatsAppWebUrl(
                    phoneNumber: '0752072772',
                    job: $job,
                    amount: 500,
                    paymentMethod: 'cash'
                );

                // Simulate storing in session (as controller does)
                session(['whatsapp_url' => $whatsappUrl]);

                // Verify session has WhatsApp URL
                $this->assertEquals($whatsappUrl, session('whatsapp_url'));
                $this->assertStringStartsWith('https://wa.me/0752072772?text=', session('whatsapp_url'));
                $this->assertStringContainsString('Payment Invoice', session('whatsapp_url'));
            }
        );
    }
}