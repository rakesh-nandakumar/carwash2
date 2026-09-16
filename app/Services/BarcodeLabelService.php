<?php

namespace App\Services;

use App\Models\BarcodeLabel;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BarcodeLabelService
{
    /**
     * Create a simple barcode label for a product
     *
     * @param  array{product_id: int, name: string|null, price: float}  $data
     */
    public function create(array $data): BarcodeLabel
    {
        $product = Product::findOrFail($data['product_id']);

        // Use product price if not provided
        $price = isset($data['price']) ? (float) $data['price'] : (float) $product->unit_price;

        if ($price <= 0) {
            throw new RuntimeException('Price must be greater than zero.');
        }

        // Generate simple code: LBL-YYYY-NNNNNN
        $year = date('Y');
        $count = DB::table('barcode_labels')
            ->whereYear('created_at', $year)
            ->count() + 1;
        $code = 'LBL-' . $year . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);

        return BarcodeLabel::create([
            'code' => $code,
            'product_id' => $product->id,
            'name' => $data['name'] ?? $product->name,
            'price' => $price,
            'printed_count' => 0,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Resolve a scanned barcode code to product information
     */
    public function resolve(string $code): ?array
    {
        $label = BarcodeLabel::with('product')
            ->where('code', $code)
            ->first();

        if (!$label) {
            return null;
        }

        return [
            'product_id' => $label->product_id,
            'product_name' => $label->product->name,
            'name' => $label->name,
            'price' => $label->price,
            'code' => $label->code,
        ];
    }

    /**
     * Mark labels as printed
     *
     * @param  \Illuminate\Support\Collection<int, BarcodeLabel>  $labels
     */
    public function markPrinted($labels): void
    {
        BarcodeLabel::whereIn('id', $labels->pluck('id'))
            ->update([
                'printed_count' => DB::raw('printed_count + 1'),
                'last_printed_at' => now(),
            ]);
    }
}
