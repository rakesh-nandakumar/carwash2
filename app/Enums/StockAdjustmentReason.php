<?php

namespace App\Enums;

enum StockAdjustmentReason: string
{
    case COUNT_ERROR = 'count_error';
    case DAMAGE = 'damage';
    case THEFT = 'theft';
    case FOUND = 'found';
    case SUPPLIER_CORRECTION = 'supplier_correction';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::COUNT_ERROR => 'Count Error',
            self::DAMAGE => 'Damage',
            self::THEFT => 'Theft',
            self::FOUND => 'Found',
            self::SUPPLIER_CORRECTION => 'Supplier Correction',
            self::OTHER => 'Other',
        };
    }
}