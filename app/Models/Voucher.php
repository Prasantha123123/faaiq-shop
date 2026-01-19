<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_category_id',
        'voucher_code',
        'quantity',
        'sale_id',
        'issued_at',
        'is_used',
        'used_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the category that owns the voucher.
     */
    public function voucherCategory()
    {
        return $this->belongsTo(VoucherCategory::class);
    }

    /**
     * Get the sale associated with this voucher.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Generate a unique random voucher code (5 characters).
     */
    public static function generateVoucherCode()
    {
        do {
            // Generate 5 random alphanumeric characters
            $code = 'VC-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5));
        } while (self::where('voucher_code', $code)->exists());

        return $code;
    }
}
