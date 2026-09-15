<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['contacts', 'addresses', 'bankAccounts', 'ledgers']);
        return view('suppliers.show', compact('supplier'));
    }
}
