<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service for generating systematic document numbers.
 * Provides consistent numbering across different document types.
 */
class DocumentNumberService
{
    /**
     * Generate the next document number for a given type.
     *
     * @param  string  $type  Document type (e.g., 'grn', 'purchase_order', 'supplier_return')
     * @return string
     */
    public function next(string $type): string
    {
        return match ($type) {
            'grn' => $this->generateGrnNumber(),
            'purchase_order' => $this->generatePurchaseOrderNumber(),
            'supplier_return' => $this->generateSupplierReturnNumber(),
            'grn_reference' => $this->generateGrnReference(),
            default => $this->generateGenericNumber($type),
        };
    }

    /**
     * Generate GRN number in format: GRN-YYYY-NNNNNN
     */
    private function generateGrnNumber(): string
    {
        $year = date('Y');
        $count = $this->getCountForYear('goods_receipts', $year) + 1;
        return 'GRN-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Purchase Order number in format: PO-YYYY-NNNNNN
     */
    private function generatePurchaseOrderNumber(): string
    {
        $year = date('Y');
        $count = $this->getCountForYear('purchase_orders', $year) + 1;
        return 'PO-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Supplier Return number in format: SR-YYYY-NNNNNN
     */
    private function generateSupplierReturnNumber(): string
    {
        $year = date('Y');
        $count = $this->getCountForYear('supplier_returns', $year) + 1;
        return 'SR-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate GRN reference number in format: REF-YYYY-NNNNNN
     */
    private function generateGrnReference(): string
    {
        $year = date('Y');
        $count = $this->getCountForYear('goods_receipts', $year) + 1;
        return 'REF-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate generic number for other document types
     */
    private function generateGenericNumber(string $type): string
    {
        $prefix = strtoupper(substr($type, 0, 3));
        $year = date('Y');
        $count = $this->getCountForYear($type . 's', $year) + 1;
        return $prefix . '-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get count of records for a given table and year
     */
    private function getCountForYear(string $table, string $year): int
    {
        return DB::table($table)
            ->whereYear('created_at', $year)
            ->count();
    }

    /**
     * Resync document sequences if needed (for data migration or correction)
     */
    public function resync(string $type): void
    {
        // This method can be used to fix sequence numbers if they get out of sync
        // Implementation depends on specific requirements
    }
}
