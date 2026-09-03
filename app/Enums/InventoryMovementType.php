<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case SERVICE_USAGE = 'service_usage';
    case RETURN = 'return';
    case DAMAGE = 'damage';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER = 'transfer';
    case SUPPLIER_RETURN = 'supplier_return';
    case CUSTOMER_RETURN = 'customer_return';
    case RESTOCK = 'restock';

    public function getLabel(): string
    {
        return match($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::SERVICE_USAGE => 'Service Usage',
            self::RETURN => 'Return',
            self::DAMAGE => 'Damage',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER => 'Transfer',
            self::SUPPLIER_RETURN => 'Supplier Return',
            self::CUSTOMER_RETURN => 'Customer Return',
            self::RESTOCK => 'Restock',
        };
    }

    public function affectsStock(): bool
    {
        return !in_array($this, [self::DAMAGE, self::ADJUSTMENT]);
    }
}
