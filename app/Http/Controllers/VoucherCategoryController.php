<?php

namespace App\Http\Controllers;

use App\Models\VoucherCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = VoucherCategory::orderBy('amount', 'asc')->get();
        
        return Inertia::render('VoucherCategories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        VoucherCategory::create($validated);

        return redirect()->back()->with('success', 'Voucher category created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VoucherCategory $voucherCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $voucherCategory->update($validated);

        return redirect()->back()->with('success', 'Voucher category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VoucherCategory $voucherCategory)
    {
        $voucherCategory->delete();

        return redirect()->back()->with('success', 'Voucher category deleted successfully.');
    }

    /**
     * Get voucher categories with available voucher counts (API endpoint).
     */
    public function getCategories()
    {
        $categories = VoucherCategory::withCount([
            'vouchers as total_count',
            'vouchers as available_count' => function ($query) {
                $query->where('is_used', false);
            }
        ])->orderBy('amount', 'asc')->get();

        return response()->json([
            'categories' => $categories
        ]);
    }
}
