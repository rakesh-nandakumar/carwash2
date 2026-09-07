<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\{Customer, Job, Communication, CommunicationTemplate, Setting};
use App\Enums\CommunicationChannel;
use App\Enums\CommunicationStatus;

class CommunicationService
{
    public function send(
        Customer $customer,
        ?Job $job,
        string $event,
        string $message,
        ?array $attachments = null
    ): array {
        $channel = $customer->preferred_channel ?: 'whatsapp';
        $status = CommunicationStatus::QUEUED;
        $error = null;
        $providerId = null;
        $metadata = ['attachments' => $attachments];

        if ($channel === CommunicationChannel::WHATSAPP->value && Settings::str('whatsapp.provider') === 'meta') {
            try {
                $r = Http::withToken(Settings::str('whatsapp.api_token'))
                    ->post(rtrim(Settings::str('whatsapp.api_url'), '/') . '/messages', [
                        'messaging_product' => 'whatsapp',
                        'to' => $customer->whatsapp ?: $customer->phone,
                        'type' => 'text',
                        'text' => ['body' => $message]
                    ]);

                $status = $r->successful() ? CommunicationStatus::SENT : CommunicationStatus::FAILED;
                $providerId = $r->json('messages.0.id');
                $error = $r->successful() ? null : $r->body();
            } catch (\Throwable $e) {
                $status = CommunicationStatus::FAILED;
                $error = $e->getMessage();
            }
        } else {
            $status = CommunicationStatus::SENT;
        }

        $comm = Communication::create([
            'customer_id' => $customer->id,
            'job_id' => $job?->id,
            'channel' => $channel,
            'direction' => 'outbound',
            'recipient' => $customer->whatsapp ?: $customer->phone,
            'message' => $message,
            'status' => $status->value,
            'provider_message_id' => $providerId,
            'error' => $error,
            'metadata' => $metadata,
            'sent_by' => auth()->id(),
            'sent_at' => $status === CommunicationStatus::SENT ? now() : null,
        ]);

        return [
            'id' => $comm->id,
            'status' => $status->value,
            'error' => $error
        ];
    }

    public function sendTemplate(Customer $customer, ?Job $job, string $event, array $data = []): array
    {
        $template = CommunicationTemplate::where('event', $event)
            ->where('channel', $customer->preferred_channel ?: 'whatsapp')
            ->where('active', true)
            ->first();

        if (!$template) {
            $template = CommunicationTemplate::where('event', $event)
                ->where('channel', 'whatsapp')
                ->where('active', true)
                ->first();
        }

        if (!$template) {
            return ['error' => 'Template not found for event: ' . $event];
        }

        $message = $this->renderTemplate($template->template, array_merge([
            'customer_name' => $customer->full_name,
            'customer_phone' => $customer->phone,
            'business_name' => Settings::str('business.company_name', 'AutoCare Pro')
        ], $data));

        return $this->send($customer, $job, $event, $message);
    }

    public function renderTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    public function notifyVehicleReceived(Job $job): void
    {
        $this->sendTemplate($job->customer, $job, 'vehicle_received', [
            'job_number' => $job->job_number,
            'vehicle' => $job->vehicle->registration_number
        ]);
    }

    public function notifyInspectionCompleted(Job $job): void
    {
        $this->sendTemplate($job->customer, $job, 'inspection_completed', [
            'job_number' => $job->job_number
        ]);
    }

    public function notifyAdditionalWorkRequired(Job $job, string $service, float $estimatedCost): void
    {
        $this->sendTemplate($job->customer, $job, 'additional_work_required', [
            'job_number' => $job->job_number,
            'service' => $service,
            'estimated_cost' => $estimatedCost
        ]);
    }

    public function notifyServiceStarted(Job $job): void
    {
        $this->sendTemplate($job->customer, $job, 'service_started', [
            'job_number' => $job->job_number
        ]);
    }

    public function notifyWaitingForParts(Job $job, string $part): void
    {
        $this->sendTemplate($job->customer, $job, 'waiting_for_parts', [
            'job_number' => $job->job_number,
            'part' => $part
        ]);
    }

    public function notifyServiceCompleted(Job $job): void
    {
        $this->sendTemplate($job->customer, $job, 'service_completed', [
            'job_number' => $job->job_number
        ]);
    }

    public function notifyVehicleReady(Job $job, float $amount): void
    {
        $this->sendTemplate($job->customer, $job, 'vehicle_ready', [
            'job_number' => $job->job_number,
            'amount' => $amount
        ]);
    }

    public function notifyPaymentReceived(Job $job, float $amount): void
    {
        $this->sendTemplate($job->customer, $job, 'payment_received', [
            'job_number' => $job->job_number,
            'amount' => $amount
        ]);
    }

    public function getCustomerTimeline(int $customerId): array
    {
        return Communication::where('customer_id', $customerId)
            ->with(['job', 'sentBy'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'channel' => $c->channel,
                    'direction' => $c->direction,
                    'message' => $c->message,
                    'status' => $c->status,
                    'created_at' => $c->created_at->format('Y-m-d H:i:s'),
                    'job_number' => $c->job?->job_number,
                    'sent_by' => $c->sentBy?->name,
                    'attachments' => $c->metadata['attachments'] ?? null
                ];
            })
            ->toArray();
    }

    public function getJobTimeline(int $jobId): array
    {
        return Communication::where('job_id', $jobId)
            ->with(['customer', 'sentBy'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'channel' => $c->channel,
                    'direction' => $c->direction,
                    'message' => $c->message,
                    'status' => $c->status,
                    'created_at' => $c->created_at->format('Y-m-d H:i:s'),
                    'customer_name' => $c->customer->full_name,
                    'sent_by' => $c->sentBy?->name
                ];
            })
            ->toArray();
    }

    public function sendInvoiceToPhoneNumber(string $phoneNumber, Job $job, float $amount, string $paymentMethod): array
    {
        $customer = $job->customer;
        $invoice = $job->invoice;

        $message = "🧾 *INVOICE DETAILS*\n\n";
        $message .= "*Job Number:* {$job->job_number}\n";
        $message .= "*Customer:* {$customer->full_name}\n";
        $message .= "*Vehicle:* {$job->vehicle->registration_number}\n\n";
        $message .= "*Payment Details:*\n";
        $message .= "Amount Paid: Rs. " . number_format($amount, 2) . "\n";
        $message .= "Payment Method: {$paymentMethod}\n";

        if ($invoice) {
            $message .= "Invoice Total: Rs. " . number_format($invoice->total, 2) . "\n";
            $message .= "Balance: Rs. " . number_format($invoice->balance, 2) . "\n";
        }

        $message .= "\nThank you for your business!";

        $channel = 'whatsapp';
        $status = CommunicationStatus::QUEUED;
        $error = null;
        $providerId = null;
        $metadata = [
            'invoice_id' => $invoice->id ?? null,
            'payment_amount' => $amount,
            'payment_method' => $paymentMethod
        ];

        // Try Meta API first
        if (Settings::str('whatsapp.provider') === 'meta') {
            try {
                $r = Http::withToken(Settings::str('whatsapp.api_token'))
                    ->post(rtrim(Settings::str('whatsapp.api_url'), '/') . '/messages', [
                        'messaging_product' => 'whatsapp',
                        'to' => $phoneNumber,
                        'type' => 'text',
                        'text' => ['body' => $message]
                    ]);

                $status = $r->successful() ? CommunicationStatus::SENT : CommunicationStatus::FAILED;
                $providerId = $r->json('messages.0.id');
                $error = $r->successful() ? null : $r->body();
            } catch (\Throwable $e) {
                $status = CommunicationStatus::FAILED;
                $error = $e->getMessage();
            }
        } else {
            // Fallback: Log as sent without actual API call
            $status = CommunicationStatus::SENT;
            $error = 'WhatsApp API not configured - message logged only';
        }

        $comm = Communication::create([
            'customer_id' => $customer->id,
            'job_id' => $job->id,
            'channel' => $channel,
            'direction' => 'outbound',
            'recipient' => $phoneNumber,
            'message' => $message,
            'status' => $status->value,
            'provider_message_id' => $providerId,
            'error' => $error,
            'metadata' => $metadata,
            'sent_by' => auth()->id(),
            'sent_at' => $status === CommunicationStatus::SENT ? now() : null,
        ]);

        return [
            'id' => $comm->id,
            'status' => $status->value,
            'error' => $error
        ];
    }

    public function sendInvoiceViaEmail(string $email, Job $job, float $amount, string $paymentMethod): array
    {
        $customer = $job->customer;
        $invoice = $job->invoice;

        $message = "INVOICE DETAILS\n\n";
        $message .= "Job Number: {$job->job_number}\n";
        $message .= "Customer: {$customer->full_name}\n";
        $message .= "Vehicle: {$job->vehicle->registration_number}\n\n";
        $message .= "Payment Details:\n";
        $message .= "Amount Paid: Rs. " . number_format($amount, 2) . "\n";
        $message .= "Payment Method: {$paymentMethod}\n";

        if ($invoice) {
            $message .= "Invoice Total: Rs. " . number_format($invoice->total, 2) . "\n";
            $message .= "Balance: Rs. " . number_format($invoice->balance, 2) . "\n";
        }

        $message .= "\nThank you for your business!";

        $channel = 'email';
        $status = CommunicationStatus::QUEUED;
        $error = null;
        $providerId = null;
        $metadata = [
            'invoice_id' => $invoice->id ?? null,
            'payment_amount' => $amount,
            'payment_method' => $paymentMethod
        ];

        try {
            Mail::raw($message, function ($message) use ($email, $job) {
                $message->to($email)
                    ->subject("Invoice for Job #{$job->job_number}")
                    ->from(config('mail.from.address', config('app.name')));
            });

            $status = CommunicationStatus::SENT;
        } catch (\Throwable $e) {
            $status = CommunicationStatus::FAILED;
            $error = $e->getMessage();
        }

        $comm = Communication::create([
            'customer_id' => $customer->id,
            'job_id' => $job->id,
            'channel' => $channel,
            'direction' => 'outbound',
            'recipient' => $email,
            'message' => $message,
            'status' => $status->value,
            'provider_message_id' => $providerId,
            'error' => $error,
            'metadata' => $metadata,
            'sent_by' => auth()->id(),
            'sent_at' => $status === CommunicationStatus::SENT ? now() : null,
        ]);

        return [
            'id' => $comm->id,
            'status' => $status->value,
            'error' => $error
        ];
    }

    public function sendInvoiceViaLogOnly(Job $job, float $amount, string $paymentMethod): array
    {
        $customer = $job->customer;
        $invoice = $job->invoice;

        $message = "🧾 *INVOICE DETAILS*\n\n";
        $message .= "*Job Number:* {$job->job_number}\n";
        $message .= "*Customer:* {$customer->full_name}\n";
        $message .= "*Vehicle:* {$job->vehicle->registration_number}\n\n";
        $message .= "*Payment Details:*\n";
        $message .= "Amount Paid: Rs. " . number_format($amount, 2) . "\n";
        $message .= "Payment Method: {$paymentMethod}\n";

        if ($invoice) {
            $message .= "Invoice Total: Rs. " . number_format($invoice->total, 2) . "\n";
            $message .= "Balance: Rs. " . number_format($invoice->balance, 2) . "\n";
        }

        $message .= "\nThank you for your business!";

        $channel = 'log';
        $status = CommunicationStatus::SENT;
        $error = null;
        $providerId = null;
        $metadata = [
            'invoice_id' => $invoice->id ?? null,
            'payment_amount' => $amount,
            'payment_method' => $paymentMethod
        ];

        $comm = Communication::create([
            'customer_id' => $customer->id,
            'job_id' => $job->id,
            'channel' => $channel,
            'direction' => 'outbound',
            'recipient' => 'system-log',
            'message' => $message,
            'status' => $status->value,
            'provider_message_id' => $providerId,
            'error' => $error,
            'metadata' => $metadata,
            'sent_by' => auth()->id(),
            'sent_at' => now(),
        ]);

        return [
            'id' => $comm->id,
            'status' => $status->value,
            'error' => $error
        ];
    }

    public function generateWhatsAppWebUrl(string $phoneNumber, Job $job, float $amount, string $paymentMethod): string
    {
        $customer = $job->customer;
        $invoice = $job->invoice;

        // Create a beautiful, professional payment notification for the boss
        $message = "💰 *PAYMENT RECEIVED NOTIFICATION*\n\n";
        
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📋 *JOB DETAILS*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "🔹 *Job Number:* {$job->job_number}\n";
        $message .= "🚗 *Vehicle:* {$job->vehicle->registration_number}\n";
        $message .= "👤 *Customer:* {$customer->full_name}\n";
        $message .= "📞 *Phone:* {$customer->phone}\n\n";
        
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💳 *PAYMENT INFORMATION*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "💵 *Amount Received:* Rs. " . number_format($amount, 2) . "\n";
        $message .= "🏦 *Payment Method:* " . strtoupper($paymentMethod) . "\n";
        
        if ($invoice) {
            $message .= "📊 *Invoice Total:* Rs. " . number_format($invoice->total, 2) . "\n";
            $message .= "⚖️ *Balance Due:* Rs. " . number_format($invoice->balance, 2) . "\n";
            
            if ($invoice->balance <= 0) {
                $message .= "✅ *Status:* FULLY PAID\n";
            } else {
                $message .= "⏳ *Status:* PARTIAL PAYMENT\n";
            }
        }
        
        $message .= "\n━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "📅 *Processed on:* " . now()->format('d M Y, H:i') . "\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        
        $message .= "\n🎉 *Payment successfully processed!*";

        // Create WhatsApp Web URL (like the job feature does)
        return "https://wa.me/{$phoneNumber}?text=" . urlencode($message);
    }
}