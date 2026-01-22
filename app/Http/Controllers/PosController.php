<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Size;
use App\Models\StockTransaction;
use App\Models\Employee;
use App\Models\Voucher;
use App\Models\VoucherCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }

        $allcategories = Category::with('parent')->get()->map(function ($category) {
            $category->hierarchy_string = $category->hierarchy_string; // Access it
            return $category;
        });
        $colors = Color::orderBy('created_at', 'desc')->get();
        $sizes = Size::orderBy('created_at', 'desc')->get();
        $allemployee = Employee::orderBy('created_at', 'desc')->get();


        // Render the page for GET requests
        return Inertia::render('Pos/Index', [
            'product' => null,
            'error' => null,
            'loggedInUser' => Auth::user(),
            'allcategories' => $allcategories,
            'allemployee' => $allemployee,
            'colors' => $colors,
            'sizes' => $sizes,
        ]);
    }

  public function getProduct(Request $request)
{
    if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
        abort(403, 'Unauthorized');
    }

    $request->validate([
        'barcode' => 'required',
    ]);

    // get all products that share this barcode OR code
    $products = Product::where('barcode', $request->barcode)
        ->orWhere('code', $request->barcode)
        ->with(['color', 'promotion_items.product'])
        ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'product' => null,
            'products' => [],
            'colorOptions' => [],
            'error' => 'Product not found',
        ]);
    }

    // build color list ONLY from these products
    $colorOptions = $products
        ->filter(fn ($p) => $p->color_id)        // only rows that actually have a color
        ->map(function ($p) {
            return [
                'product_id' => $p->id,          // we keep this to know which product matches which color
                'color_id'   => $p->color_id,
                'color_name' => optional($p->color)->name,
            ];
        })
        ->unique('color_id')                     // avoid duplicates
        ->values();

    // if only one product → return it as before, but ALSO send colorOptions
    if ($products->count() === 1) {
        return response()->json([
            'product'      => $products->first(),
            'products'     => [],
            'colorOptions' => $colorOptions,
            'error'        => null,
        ]);
    }

    // multiple products (same barcode, different colors)
    return response()->json([
        'product'      => null,
        'products'     => $products,
        'colorOptions' => $colorOptions,
        'error'        => null,
    ]);
}


    public function getCoupon(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The coupon code missing.', 'code.string' => 'The coupon code must be a valid string.']
        );

        $coupon = Coupon::where('code', $request->code)->first();

        if (!$coupon) {
            return response()->json(['error' => 'Invalid coupon code.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['error' => 'Coupon is expired or has been fully used.']);
        }

        return response()->json(['success' => 'Coupon applied successfully.', 'coupon' => $coupon]);
    }

    public function getVoucher(Request $request)
    {
        $request->validate(
            ['code' => 'required|string'],
            ['code.required' => 'The voucher code is missing.', 'code.string' => 'The voucher code must be a valid string.']
        );

        $voucher = Voucher::with('voucherCategory')
            ->where('voucher_code', $request->code)
            ->first();

        if (!$voucher) {
            return response()->json(['error' => 'Invalid voucher code.']);
        }

        if ($voucher->is_used) {
            return response()->json(['error' => 'This voucher has already been used.']);
        }

        // Return voucher with category amount
        return response()->json([
            'success' => 'Voucher applied successfully.',
            'voucher' => [
                'id' => $voucher->id,
                'voucher_code' => $voucher->voucher_code,
                'amount' => $voucher->voucherCategory->amount,
                'category_name' => $voucher->voucherCategory->name,
            ]
        ]);
    }

    public function submit(Request $request)
    {

        if (!Gate::allows('hasRole', ['Admin', 'Cashier'])) {
            abort(403, 'Unauthorized');
        }
        // Combine countryCode and contactNumber to create the phone field


        $customer = null;

        $products = $request->input('products');
        $totalAmount = collect($products)->reduce(function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['selling_price']);
        }, 0);

        $totalCost = collect($products)->reduce(function ($carry, $product) {
            return $carry + ($product['quantity'] * $product['cost_price']);
        }, 0);

        $productDiscounts = collect($products)->reduce(function ($carry, $product) {
            if (isset($product['discount']) && $product['discount'] > 0 && isset($product['apply_discount']) && $product['apply_discount'] != false) {
                $discountAmount = ($product['selling_price'] - $product['discounted_price']) * $product['quantity'];
                return $carry + $discountAmount;
            }
            return $carry;
        }, 0);

        // Get coupon discount if applied
        $couponDiscount = isset($request->input('appliedCoupon')['discount']) ?
            floatval($request->input('appliedCoupon')['discount']) : 0;

        // Get voucher payment amount if applied (NOT a discount, it's a payment method)
        $voucherPaymentAmount = 0;
        $voucherId = $request->input('voucher_id');
        if ($voucherId) {
            $voucher = Voucher::with('voucherCategory')->find($voucherId);
            if ($voucher && !$voucher->is_used) {
                $voucherPaymentAmount = floatval($voucher->voucherCategory->amount);
            }
        }

        // Calculate total combined discount (excluding voucher as it's a payment method)
        $totalDiscount = $productDiscounts + $couponDiscount;

        DB::beginTransaction(); // Start a transaction

        try {
            // Save the customer data to the database
            if ($request->input('customer.contactNumber') || $request->input('customer.name') || $request->input('customer.email')) {

                $phone = $request->input('customer.countryCode') . $request->input('customer.contactNumber');
                $customer = Customer::where('email', $request->input('customer.email'))->first();
                $customer1 = Customer::where('phone', $phone)->first();

                if ($customer) {
                    $email = '';
                } else {
                    $email = $request->input('customer.email');
                }

                if ($customer1) {
                    $phone = '';
                }

                if (!empty($phone) || !empty($email) || !empty($request->input('customer.name'))) {
                    $customer = Customer::create([
                        'name' => $request->input('customer.name'),
                        'email' => $email,
                        'phone' => $phone,
                        'address' => $request->input('customer.address', ''), // Optional address
                        'member_since' => now()->toDateString(), // Current date
                        'loyalty_points' => 0, // Default value
                    ]);
                }
            }

            // Check if any products are vouchers
            $hasVouchers = collect($products)->contains(function ($product) {
                return isset($product['is_voucher']) && $product['is_voucher'] === true;
            });

            $voucherCategories = [];

            // Create the sale record
            $sale = Sale::create([
                'customer_id' => $customer ? $customer->id : null, // Nullable customer_id
                'employee_id' => $request->input('employee_id'),
                'user_id' => $request->input('userId'), // Logged-in user ID
                'order_id' => $request->input('orderid'),
                'total_amount' => $totalAmount, // Total amount of the sale
                'discount' => $totalDiscount, // Default discount to 0 if not provided
                'total_cost' => $totalCost,
                'payment_method' => $request->input('paymentMethod'), // Payment method from the request
                'sale_date' => now()->toDateString(), // Current date
                'cash' => $request->input('cash'),
                'custom_discount' => $request->input('custom_discount'),
                'custom_discount_type' => $request->input('custom_discount_type', 'fixed'),
                'has_vouchers' => $hasVouchers,
                'paid_with_voucher' => $voucherId ? true : false,
                'redeemed_voucher_id' => $voucherId,
                'voucher_payment_amount' => $request->input('voucher_amount', 0),
            ]);

            // Mark voucher as used if applied
            if ($voucherId) {
                $voucher = Voucher::find($voucherId);
                if ($voucher && !$voucher->is_used) {
                    $voucher->update([
                        'is_used' => true,
                        'used_at' => now(),
                        'sale_id' => $sale->id,
                    ]);
                }
            }

            foreach ($products as $product) {
                // Check if this is a voucher purchase
                if (isset($product['is_voucher']) && $product['is_voucher'] === true) {
                    // Handle voucher purchase
                    $voucherCategoryId = $product['voucher_category_id'];
                    $quantity = $product['quantity'];
                    
                    // Get available vouchers for this category
                    $availableVouchers = Voucher::where('voucher_category_id', $voucherCategoryId)
                        ->where('is_used', false)
                        ->limit($quantity)
                        ->get();
                    
                    if ($availableVouchers->count() < $quantity) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient vouchers available. Only {$availableVouchers->count()} available.",
                        ], 423);
                    }
                    
                    // Mark vouchers as sold
                    foreach ($availableVouchers as $voucher) {
                        $voucher->update([
                            'sale_id' => $sale->id,
                            'issued_at' => now(),
                        ]);
                    }
                    
                    // Activate the voucher category when first voucher is sold
                    $voucherCategory = VoucherCategory::find($voucherCategoryId);
                    if ($voucherCategory && !$voucherCategory->is_active) {
                        $voucherCategory->update(['is_active' => true]);
                    }
                    
                    // Track voucher category info
                    $voucherCategories[] = [
                        'id' => $voucherCategoryId,
                        'name' => $voucherCategory->name,
                        'amount' => $voucherCategory->amount,
                        'quantity' => $quantity,
                    ];
                    
                    // Create sale item for voucher
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => null, // No product for vouchers
                        'quantity' => $quantity,
                        'unit_price' => $product['selling_price'],
                        'total_price' => $quantity * $product['selling_price'],
                    ]);
                    
                    continue; // Skip regular product processing
                }
                
                // Check stock before saving sale items
                $productModel = Product::find($product['id']);
                if ($productModel) {
                    $newStockQuantity = $productModel->stock_quantity - $product['quantity'];

                    // Prevent stock from going negative
                    if ($newStockQuantity < 0) {
                        // Rollback transaction and return error
                        DB::rollBack();
                        return response()->json([
                            'message' => "Insufficient stock for product: {$productModel->name}
                            ({$productModel->stock_quantity} available)",
                        ], 423);
                    }

                    if ($productModel->expire_date && now()->greaterThan($productModel->expire_date)) {
                        // Rollback transaction and return error
                        DB::rollBack();
                        return response()->json([
                            'message' => "The product '{$productModel->name}' has expired (Expiration Date: {$productModel->expire_date->format('Y-m-d')}).",
                        ], 423); // HTTP 422 Unprocessable Entity
                    }

                    // Create sale item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['selling_price'],
                        'total_price' => $product['quantity'] * $product['selling_price'],
                    ]);

                    StockTransaction::create([
                        'product_id' => $product['id'],
                        'transaction_type' => 'Sold',
                        'quantity' => $product['quantity'],
                        'transaction_date' => now(),
                        'supplier_id' => $productModel->supplier_id ?? null,
                    ]);

                    // Update stock quantity
                    $productModel->update([
                        'stock_quantity' => $newStockQuantity,
                    ]);
                }
            }

            // Update sale with voucher categories if any were sold
            if (!empty($voucherCategories)) {
                $sale->update([
                    'voucher_categories' => json_encode($voucherCategories),
                ]);
            }

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Sale recorded successfully!'], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'message' => 'Customer details saved successfully!',
            'data' => $customer,
        ], 201);
    }
}
