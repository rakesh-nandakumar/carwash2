<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use App\Services\CurrentContext;

class AuditService
{
    public function log(string $eventKey, string $description, string $severity = 'info', ?string $actorType = null, ?string $actorEmail = null, ?array $meta = null, bool $isFlagged = false, ?int $tenantId = null): void
    {
        $context = app(CurrentContext::class);
        
        // Debug logging to understand context state
        \Log::debug('AuditService::log called', [
            'event_key' => $eventKey,
            'provided_tenant_id' => $tenantId,
            'context_tenant_id' => $context->tenantId(),
            'authenticated' => auth()->check(),
            'user_type' => auth()->check() ? get_class(auth()->user()) : 'none',
            'user_tenant_id' => auth()->check() && auth()->user() instanceof \App\Models\User ? auth()->user()->tenant_id : 'n/a',
            'is_central' => $context->isCentral(),
        ]);
        
        // If no tenant_id is provided, try to get it from the current context first
        if ($tenantId === null) {
            $tenantId = $context->tenantId();
        }
        
        // If still no tenant_id, try to get it from the authenticated regular user (not central admin)
        if ($tenantId === null && auth()->check() && auth()->user() instanceof \App\Models\User && auth()->user()->tenant_id) {
            $tenantId = auth()->user()->tenant_id;
        }
        
        // If we still don't have a tenant_id, we can't create the audit log
        if ($tenantId === null) {
            // Log to Laravel's log instead of throwing an error
            \Log::warning('Audit log skipped - no tenant context', [
                'event_key' => $eventKey,
                'description' => $description,
                'authenticated' => auth()->check(),
                'user_type' => auth()->check() ? get_class(auth()->user()) : 'none',
                'context_tenant_id' => $context->tenantId(),
                'is_central' => $context->isCentral(),
            ]);
            return;
        }
        
        $createLog = function () use ($eventKey, $severity, $description, $actorEmail, $actorType, $meta, $isFlagged) {
            AuditLog::create([
                'event_key' => $eventKey,
                'severity' => $severity,
                'description' => $description,
                'actor_email' => $actorEmail ?? (auth()->check() ? auth()->user()->email : null),
                'actor_type' => $actorType ?? (auth()->check() ? 'tenant_user' : null),
                'ip_address' => Request::ip(),
                'meta' => $meta,
                'is_flagged' => $isFlagged,
            ]);
        };
        
        // Always run in the tenant's context to ensure BelongsToTenant works correctly
        $context->runForTenant($tenantId, $createLog);
    }

    public function logLogin(): void
    {
        $this->log('auth.login', 'User logged in', 'info', 'tenant_user', auth()->user()->email);
    }

    public function logLogout(): void
    {
        $this->log('auth.logout', 'User logged out', 'info', 'tenant_user', auth()->user()->email);
    }

    public function logInvoiceCreation(int $invoiceId, array $invoiceData): void
    {
        $this->log('invoice.created', "Invoice #{$invoiceId} created", 'info', 'tenant_user', auth()->user()->email, [
            'invoice_id' => $invoiceId,
            'invoice_data' => $invoiceData,
        ]);
    }

    public function logInvoiceModification(int $invoiceId, array $oldData, array $newData, string $reason): void
    {
        $this->log('invoice.modified', "Invoice #{$invoiceId} modified: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'invoice_id' => $invoiceId,
            'old_data' => $oldData,
            'new_data' => $newData,
            'reason' => $reason,
        ]);
    }

    public function logPayment(int $paymentId, array $paymentData): void
    {
        $this->log('payment.created', "Payment #{$paymentId} created", 'info', 'tenant_user', auth()->user()->email, [
            'payment_id' => $paymentId,
            'payment_data' => $paymentData,
        ]);
    }

    public function logRefund(int $refundId, array $refundData): void
    {
        $this->log('payment.refunded', "Refund #{$refundId} processed", 'warning', 'tenant_user', auth()->user()->email, [
            'refund_id' => $refundId,
            'refund_data' => $refundData,
        ]);
    }

    public function logDiscountApplied(int $invoiceId, array $discountData, string $reason): void
    {
        $this->log('invoice.discount_applied', "Discount applied to invoice #{$invoiceId}: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'invoice_id' => $invoiceId,
            'discount_data' => $discountData,
            'reason' => $reason,
        ]);
    }

    public function logStockAdjustment(int $productId, int $branchId, float $oldQty, float $newQty, string $reason): void
    {
        $this->log('inventory.adjusted', "Stock adjusted for product #{$productId}: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'product_id' => $productId,
            'branch_id' => $branchId,
            'old_quantity' => $oldQty,
            'new_quantity' => $newQty,
            'reason' => $reason,
        ]);
    }

    public function logServiceRemoval(int $jobServiceId, array $serviceData, string $reason): void
    {
        $this->log('job.service_removed', "Service #{$jobServiceId} removed from job: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'job_service_id' => $jobServiceId,
            'service_data' => $serviceData,
            'reason' => $reason,
        ]);
    }

    public function logServiceAddition(int $jobServiceId, array $serviceData): void
    {
        $this->log('job.service_added', "Service #{$jobServiceId} added to job", 'info', 'tenant_user', auth()->user()->email, [
            'job_service_id' => $jobServiceId,
            'service_data' => $serviceData,
        ]);
    }

    public function logPriceChange(int $entityId, string $entityType, float $oldPrice, float $newPrice, string $reason): void
    {
        $this->log('price.changed', "Price changed for {$entityType} #{$entityId}: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'entity_id' => $entityId,
            'entity_type' => $entityType,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'reason' => $reason,
        ]);
    }

    public function logServiceCancellation(int $jobId, string $reason): void
    {
        $this->log('job.cancelled', "Job #{$jobId} cancelled: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'job_id' => $jobId,
            'reason' => $reason,
        ]);
    }

    public function logCustomerDeletion(int $customerId, array $customerData): void
    {
        $this->log('customer.deleted', "Customer #{$customerId} deleted", 'critical', 'tenant_user', auth()->user()->email, [
            'customer_id' => $customerId,
            'customer_data' => $customerData,
        ], true);
    }

    public function logPermissionChange(int $userId, array $oldPermissions, array $newPermissions, string $reason): void
    {
        $this->log('user.permissions_changed', "Permissions changed for user #{$userId}: {$reason}", 'warning', 'tenant_user', auth()->user()->email, [
            'user_id' => $userId,
            'old_permissions' => $oldPermissions,
            'new_permissions' => $newPermissions,
            'reason' => $reason,
        ]);
    }

    public function getAuditLogs(?string $eventKey = null, ?string $actorEmail = null, ?int $limit = 100): array
    {
        $query = AuditLog::query();

        if ($eventKey) {
            $query->where('event_key', $eventKey);
        }

        if ($actorEmail) {
            $query->where('actor_email', $actorEmail);
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event_key' => $log->event_key,
                    'severity' => $log->severity,
                    'description' => $log->description,
                    'actor_email' => $log->actor_email,
                    'actor_type' => $log->actor_type,
                    'ip_address' => $log->ip_address,
                    'is_flagged' => $log->is_flagged,
                    'meta' => $log->meta,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    public function getEntityHistory(string $eventKeyPattern, ?int $limit = 100): array
    {
        return AuditLog::where('event_key', 'like', $eventKeyPattern)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'event_key' => $log->event_key,
                    'severity' => $log->severity,
                    'description' => $log->description,
                    'actor_email' => $log->actor_email,
                    'actor_type' => $log->actor_type,
                    'meta' => $log->meta,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    public function getUserActivity(string $actorEmail, ?int $days = 30): array
    {
        return AuditLog::where('actor_email', $actorEmail)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                return [
                    'event_key' => $log->event_key,
                    'severity' => $log->severity,
                    'description' => $log->description,
                    'meta' => $log->meta,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }
}
