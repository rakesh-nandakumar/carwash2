# GRN and Supplier Implementation Documentation

## Overview

This document provides comprehensive documentation for the GRN (Goods Receipt Note) and Supplier functionality implemented in the autocare project. The implementation follows patterns from the point-filament repository but is adapted for the autocare project's structure without batch tracking complexity.

## Architecture

### Core Components

#### Models
- **Supplier**: Main supplier model with financial tracking
- **SupplierContact**: Contact persons for suppliers
- **SupplierAddress**: Multiple addresses with types (billing, shipping)
- **SupplierBankAccount**: Bank account details
- **SupplierLedger**: Immutable financial transaction ledger
- **GoodsReceipt**: GRN with workflow states (draft → confirmed → deleted)
- **GoodsReceiptItem**: Line items for GRNs

#### Services
- **SupplierService**: Financial operations (debit/credit, blacklist/unblacklist)
- **GrnService**: Core GRN operations (create, confirm, delete)
- **DocumentNumberService**: Systematic document numbering
- **PurchaseOrderService**: Enhanced with GRN integration

#### Controllers
- **GrnController**: API endpoints for GRN management
- **SupplierController**: API endpoints for supplier management

#### Validation
- **StoreGrnRequest**: Validation for GRN creation
- **UpdateGrnRequest**: Validation for GRN updates
- **StoreSupplierRequest**: Validation for supplier creation
- **UpdateSupplierRequest**: Validation for supplier updates

## Database Schema

### Tables Created/Enhanced

1. **suppliers** (Enhanced)
   - Added UUID, business details, financial fields
   - Added status management with blacklist functionality
   - Added audit fields and soft deletes

2. **supplier_ledgers** (New)
   - Immutable financial transaction ledger
   - Tracks debits/credits with running balance

3. **supplier_contacts** (New)
   - Contact persons with designation, phone, email
   - Primary contact designation

4. **supplier_addresses** (New)
   - Multiple addresses with types (billing, shipping)
   - Primary address designation

5. **supplier_bank_accounts** (New)
   - Bank account details with primary designation

6. **goods_receipts** (Enhanced)
   - Added GRN workflow fields (status, confirmation, deletion)
   - Added supplier relationship
   - Status-based workflow

7. **goods_receipt_items** (New)
   - Line items for GRNs with quantity, costs, prices

8. **settings** (Seeded)
   - GRN statuses (draft, confirmed, deleted)
   - Supplier statuses (active, blacklisted)
   - Ledger entry types (debit, credit)
   - Address types and payment terms

## GRN Workflow

### 1. Create Draft GRN
```php
$grn = app(GrnService::class)->create(
    header: [
        'branch_id' => 1,
        'supplier_id' => 1,
        'purchase_order_id' => 1,
        'reference' => 'INV-12345',
        'note' => 'Emergency stock',
    ],
    lines: [
        [
            'product_id' => 1,
            'quantity' => 10.5,
            'unit_cost' => 150.00,
            'sale_price' => 200.00,
        ],
    ]
);
```

### 2. Confirm GRN
```php
$confirmedGrn = app(GrnService::class)->confirm($grn, auth()->id());
```
**What happens on confirmation:**
- Creates inventory movements
- Updates product sale prices
- Debits supplier ledger
- Updates purchase order status
- Changes GRN status to confirmed

### 3. Delete GRN
```php
app(GrnService::class)->delete($grn, auth()->id());
```
**What happens on deletion:**
- If confirmed: reverses stock movements, credits supplier ledger
- Changes GRN status to deleted
- Sets deletion audit fields

## Supplier Financial Operations

### Debit (Increase Balance)
```php
app(SupplierService::class)->debit(
    $supplier, 
    5000.00, 
    $grn, 
    "GRN received"
);
```
- Increases outstanding balance
- Creates ledger entry
- Used when GRN is confirmed

### Credit (Decrease Balance)
```php
app(SupplierService::class)->credit(
    $supplier, 
    2000.00, 
    $payment, 
    "Payment received"
);
```
- Decreases outstanding balance (never below zero)
- Creates ledger entry
- Used when payment is made

### Blacklist/Unblacklist
```php
app(SupplierService::class)->blacklist($supplier, "Late payments");
app(SupplierService::class)->unblacklist($supplier);
```

## API Endpoints

### GRN Endpoints
- `GET /grns` - List GRNs with filtering
- `POST /grns` - Create new GRN
- `GET /grns/{grn}` - Show GRN details
- `PUT /grns/{grn}` - Update draft GRN
- `POST /grns/{grn}/confirm` - Confirm GRN
- `DELETE /grns/{grn}` - Delete GRN
- `GET /grns/statistics` - GRN statistics

### Supplier Endpoints
- `GET /suppliers` - List suppliers
- `POST /suppliers` - Create supplier
- `GET /suppliers/{supplier}` - Show supplier details
- `PUT /suppliers/{supplier}` - Update supplier
- `DELETE /suppliers/{supplier}` - Delete supplier
- `POST /suppliers/{supplier}/blacklist` - Blacklist supplier
- `POST /suppliers/{supplier}/unblacklist` - Unblacklist supplier
- `GET /suppliers/{supplier}/ledger` - Get supplier ledger
- `GET /suppliers/statistics` - Supplier statistics

## Installation

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Settings
The settings migration will automatically seed required lookup values:
- GRN statuses
- Supplier statuses
- Ledger entry types
- Address types
- Payment terms

### 3. Update Permissions
Add the following permissions to your permission system:
- `suppliers.access`, `suppliers.create`, `suppliers.edit`, `suppliers.delete`, `suppliers.blacklist`
- `grns.access`, `grns.create`, `grns.edit`, `grns.confirm`, `grns.delete`

## Testing

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --tests/Unit/GrnServiceTest.php
php artisan test --tests/Unit/SupplierServiceTest.php
php artisan test --tests/Feature/GrnWorkflowTest.php
```

### Test Coverage
- **GrnServiceTest**: Unit tests for GRN service operations
- **SupplierServiceTest**: Unit tests for supplier financial operations
- **GrnWorkflowTest**: Integration tests for complete GRN workflows

## Usage Examples

### Example 1: Complete GRN Workflow
```php
// 1. Create supplier
$supplier = Supplier::create([
    'name' => 'Auto Parts Ltd',
    'phone' => '+94 77 123 4567',
    'email' => 'info@autoparts.lk',
    'credit_limit' => 50000.00,
    'business_id' => 1,
]);

// 2. Create GRN
$grn = app(GrnService::class)->create(
    header: [
        'branch_id' => 1,
        'supplier_id' => $supplier->id,
        'reference' => 'PO-2024-001',
    ],
    lines: [
        [
            'product_id' => 1,
            'quantity' => 50,
            'unit_cost' => 150.00,
            'sale_price' => 200.00,
        ],
    ]
);

// 3. Confirm GRN
$confirmedGrn = app(GrnService::class)->confirm($grn, auth()->id());

// 4. Check supplier balance
$supplier->refresh();
echo "Outstanding balance: " . $supplier->outstanding_balance; // 7500.00
```

### Example 2: Supplier Payment
```php
// After confirming GRN, make a payment
app(SupplierService::class)->credit(
    $supplier,
    5000.00,
    $payment,
    "Payment for GRN-2024-000001"
);

$supplier->refresh();
echo "New balance: " . $supplier->outstanding_balance; // 2500.00
```

### Example 3: Check Credit Limit
```php
if (app(SupplierService::class)->isOverLimit($supplier, 10000.00)) {
    // Supplier would exceed credit limit
    // Show warning or block transaction
}
```

## Document Numbering

The DocumentNumberService provides systematic numbering:

- **GRN Numbers**: `GRN-YYYY-NNNNNN` (e.g., GRN-2024-000001)
- **PO Numbers**: `PO-YYYY-NNNNNN` (e.g., PO-2024-000001)
- **SR Numbers**: `SR-YYYY-NNNNNN` (e.g., SR-2024-000001)

## Audit Logging

All major operations are logged:
- GRN creation, confirmation, deletion
- Supplier blacklist/unblacklist
- Ledger entries

Audit logs include:
- Event key
- Description
- Actor information
- Metadata with relevant details
- IP address

## Error Handling

### Common Errors
- **Only draft GRNs can be confirmed**: GRN must be in draft status
- **GRN is already deleted**: Cannot delete an already deleted GRN
- **Debit/Credit amount must be positive**: Financial operations require positive amounts
- **Insufficient stock**: GRN confirmation validates stock availability

### Validation Rules
- GRN items require at least one item
- Quantities must be greater than 0.01
- Sale prices must be greater than 0.01
- Supplier names are required
- Contact names are required when creating contacts

## Multi-Tenant Support

All models use the `BelongsToTenant` trait, ensuring proper tenant scoping:
- Data isolation between tenants
- Automatic tenant ID assignment
- Context-aware queries

## Performance Considerations

- **Database Transactions**: All major operations use database transactions
- **Row Locking**: Supplier ledger operations use row locking to prevent race conditions
- **Eager Loading**: Controllers use eager loading to prevent N+1 queries
- **Indexing**: Database tables are properly indexed for common queries

## Future Enhancements

Potential areas for future enhancement:
- GRN return functionality
- Bulk GRN creation
- GRN export to PDF/Excel
- Supplier performance analytics
- Automated payment reminders
- Integration with accounting systems

## Troubleshooting

### Common Issues

**Issue**: GRN confirmation fails with "no sale price on file"
**Solution**: Ensure sale_price is set for all GRN items before confirmation

**Issue**: Supplier balance doesn't update
**Solution**: Check that supplier_id is properly set on GRN and status is confirmed

**Issue**: Document numbers are not sequential
**Solution**: Check DocumentNumberService year-based counting logic

**Issue**: Audit logs not appearing
**Solution**: Verify AuditService is properly configured and tenant context is available

## Support

For issues or questions:
1. Check the test files for usage examples
2. Review the database migration files for schema details
3. Examine the service classes for business logic
4. Check the audit logs for operation traces
