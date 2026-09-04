<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(string $action, ?string $entityType = null, ?int $entityId = null, $oldValue = null, $newValue = null, ?string $reason = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'reason' => $reason,
            'ip' => Request::ip(),
        ]);
    }

    public function logLogin(): void
    {
        $this->log('login', 'User', auth()->id());
    }

    public function logLogout(): void
    {
        $this->log('logout', 'User', auth()->id());
    }

    public function logInvoiceCreation(int $invoiceId, array $invoiceData): void
    {
        $this->log('invoice_created', 'Invoice', $invoiceId, null, $invoiceData);
    }

    public function logInvoiceModification(int $invoiceId, array $oldData, array $newData, string $reason): void
    {
        $this->log('invoice_modified', 'Invoice', $invoiceId, $oldData, $newData, $reason);
    }

    public function logPayment(int $paymentId, array $paymentData): void
    {
        $this->log('payment_created', 'Payment', $paymentId, null, $paymentData);
    }

    public function logRefund(int $refundId, array $refundData): void
    {
        $this->log('refund_created', 'Refund', $refundId, null, $refundData);
    }

    public function logDiscountApplied(int $invoiceId, array $discountData, string $reason): void
    {
        $this->log('discount_applied', 'Invoice', $invoiceId, null, $discountData, $reason);
    }

    public function logStockAdjustment(int $productId, int $branchId, float $oldQty, float $newQty, string $reason): void
    {
        $this->log('stock_adjusted', 'Inventory', null, [
            'product_id' => $productId,
            'branch_id' => $branchId,
            'old_quantity' => $oldQty,
        ], [
            'product_id' => $productId,
            'branch_id' => $branchId,
            'new_quantity' => $newQty,
        ], $reason);
    }

    public function logServiceRemoval(int $jobServiceId, array $serviceData, string $reason): void
    {
        $this->log('service_removed', 'JobService', $jobServiceId, $serviceData, null, $reason);
    }

    public function logServiceAddition(int $jobServiceId, array $serviceData): void
    {
        $this->log('service_added', 'JobService', $jobServiceId, null, $serviceData);
    }

    public function logPriceChange(int $entityId, string $entityType, float $oldPrice, float $newPrice, string $reason): void
    {
        $this->log('price_changed', $entityType, $entityId, ['old_price' => $oldPrice], ['new_price' => $newPrice], $reason);
    }

    public function logServiceCancellation(int $jobId, string $reason): void
    {
        $this->log('job_cancelled', 'Job', $jobId, null, null, $reason);
    }

    public function logCustomerDeletion(int $customerId, array $customerData): void
    {
        $this->log('customer_deleted', 'Customer', $customerId, $customerData, null);
    }

    public function logPermissionChange(int $userId, array $oldPermissions, array $newPermissions, string $reason): void
    {
        $this->log('permissions_changed', 'User', $userId, $oldPermissions, $newPermissions, $reason);
    }

    public function getAuditLogs(?string $entityType = null, ?int $entityId = null, ?int $userId = null, ?int $limit = 100): array
    {
        $query = AuditLog::with('user');

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user->name,
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'reason' => $log->reason,
                    'ip' => $log->ip,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    public function getEntityHistory(string $entityType, int $entityId): array
    {
        return $this->getAuditLogs($entityType, $entityId);
    }

    public function getUserActivity(int $userId, ?int $days = 30): array
    {
        return AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->with('user')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($log) {
                return [
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'reason' => $log->reason,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }
}
