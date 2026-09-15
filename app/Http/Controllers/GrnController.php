<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use Illuminate\Http\Request;

class GrnController extends Controller
{
    public function index()
    {
        return view('grns.index');
    }

    public function show(GoodsReceipt $grn)
    {
        $grn->load(['items.product', 'supplier', 'status']);
        return view('grns.show', [
            'grn' => $grn,
            'isShowPage' => true
        ]);
    }
}
