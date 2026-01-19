<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::with(['voucherCategory', 'sale'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('voucher_category_id')
            ->map(function ($group) {
                return [
                    'category' => $group->first()->voucherCategory,
                    'total_vouchers' => $group->count(),
                    'used_vouchers' => $group->where('is_used', true)->count(),
                    'available_vouchers' => $group->where('is_used', false)->count(),
                    'vouchers' => $group,
                ];
            })
            ->values();

        $categories = VoucherCategory::all();

        return Inertia::render('Vouchers/Index', [
            'voucherGroups' => $vouchers,
            'categories' => $categories
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = VoucherCategory::all();
        
        return Inertia::render('Vouchers/Create', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'voucher_category_id' => 'required|exists:voucher_categories,id',
            'quantity' => 'required|integer|min:1|max:1000',
            'sale_id' => 'nullable|exists:sales,id',
        ]);

        try {
            DB::beginTransaction();

            $vouchers = [];
            for ($i = 0; $i < $validated['quantity']; $i++) {
                $vouchers[] = [
                    'voucher_category_id' => $validated['voucher_category_id'],
                    'voucher_code' => Voucher::generateVoucherCode(),
                    'quantity' => 1,
                    'sale_id' => $validated['sale_id'] ?? null,
                    'issued_at' => now(),
                    'is_used' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Voucher::insert($vouchers);

            DB::commit();

            return redirect()->route('vouchers.index')->with('success', "{$validated['quantity']} vouchers created successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VoucherCategory $voucherCategory)
    {
        $vouchers = Voucher::where('voucher_category_id', $voucherCategory->id)
            ->with('sale')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Vouchers/Show', [
            'category' => $voucherCategory,
            'vouchers' => $vouchers
        ]);
    }

    /**
     * Mark voucher as used.
     */
    public function markAsUsed(Voucher $voucher)
    {
        $voucher->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Voucher marked as used.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        if ($voucher->is_used) {
            return redirect()->back()->with('error', 'Cannot delete a used voucher.');
        }

        $voucher->delete();

        return redirect()->back()->with('success', 'Voucher deleted successfully.');
    }
}
