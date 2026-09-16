<?php

namespace App\Http\Controllers;

use App\Models\BarcodeLabel;
use App\Models\Product;
use App\Services\BarcodeLabelService;
use Illuminate\Http\Request;

class BarcodeLabelController extends Controller
{
    public function __construct(
        private readonly BarcodeLabelService $barcodeLabelService,
    ) {}

    /**
     * Display a listing of barcode labels
     */
    public function index()
    {
        $labels = BarcodeLabel::with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('barcode-labels.index', compact('labels'));
    }

    /**
     * Show the form for creating a new barcode label
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('barcode-labels.create', compact('products'));
    }

    /**
     * Store a newly created barcode label
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'nullable|string|max:150',
            'price' => 'required|numeric|min:0.01',
        ]);

        $label = $this->barcodeLabelService->create($request->all());

        return redirect()
            ->route('barcode-labels.index')
            ->with('success', 'Barcode label created successfully.');
    }

    /**
     * Display the specified barcode label
     */
    public function show(BarcodeLabel $barcodeLabel)
    {
        return view('barcode-labels.show', compact('barcodeLabel'));
    }

    /**
     * Remove the specified barcode label
     */
    public function destroy(BarcodeLabel $barcodeLabel)
    {
        $barcodeLabel->delete();

        return redirect()
            ->route('barcode-labels.index')
            ->with('success', 'Barcode label deleted successfully.');
    }

    /**
     * Print barcode labels
     */
    public function print(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
            'copies' => 'required|integer|min:1|max:200',
        ]);

        $ids = collect(explode(',', $request->ids))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->take(500);

        if ($ids->isEmpty()) {
            abort(404, 'No labels to print.');
        }

        $labels = BarcodeLabel::with('product')
            ->whereIn('id', $ids)
            ->orderBy('code')
            ->get();

        if ($labels->isEmpty()) {
            abort(404, 'No labels to print.');
        }

        $this->barcodeLabelService->markPrinted($labels);

        return view('barcode-labels.print', [
            'labels' => $labels,
            'copies' => $request->copies,
        ]);
    }
}
