<?php

namespace App\Http\Controllers;

use App\Models\Till;
use Illuminate\Http\Request;

class TillController extends Controller
{
    public function edit()
    {
        $till = Till::query()
            ->where('code', 'MAIN')
            ->firstOrFail();

        return view('cashier.till', compact('till'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $till = Till::query()
            ->where('code', 'MAIN')
            ->firstOrFail();

        $till->update([
            'opening_balance' => $data['opening_balance'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Till settings updated successfully.');
    }
}