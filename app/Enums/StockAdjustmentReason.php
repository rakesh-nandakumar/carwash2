<?php

namespace App\Enums;

enum StockAdjustmentReason: string
{
    case COUNT_ERROR = 'count_error';
    case FOUND = 'found';
    case SUPPLIER_CORRECTION = 'supplier_correction';
    case EMERGENCY_STOCK_RECEIVED = 'emergency_stock_received';
    case TRANSFER_IN = 'transfer_in';
    case CUSTOMER_RETURN = 'customer_return';

    public function label(): string
    {
        return match ($this) {
            self::COUNT_ERROR => 'Count Error',
            self::FOUND => 'Found',
            self::SUPPLIER_CORRECTION => 'Supplier Correction',
            self::EMERGENCY_STOCK_RECEIVED => 'Emergency Stock Received',
            self::TRANSFER_IN => 'Transfer In',
            self::CUSTOMER_RETURN => 'Customer Return',
        };
    }
}