<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'user_id',
        'order_id',
        'total_amount',
        'discount',
        'payment_method',
        'sale_date',
        'total_cost',
        'cash',
        'custom_discount',
        'custom_discount_type',
        'has_vouchers',
        'voucher_categories',
        'paid_with_voucher',
        'redeemed_voucher_id',
        'voucher_payment_amount',
    ];

    protected $casts = [
        'has_vouchers' => 'boolean',
        'voucher_categories' => 'array',
        'paid_with_voucher' => 'boolean',
        'voucher_payment_amount' => 'decimal:2',
    ];




    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id','id');
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class, 'order_id','id');
    }
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function redeemedVoucher()
    {
        return $this->belongsTo(Voucher::class, 'redeemed_voucher_id', 'id');
    }
}
