<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'transaction_type',
        'quantity',
        'transaction_date',
        'supplier_id',
        'reason',
    ];

     // Relationships
     public function product()
     {
         // StockTransaction belongs to a Product via product_id
         return $this->belongsTo(Product::class, 'product_id', 'id');
     }

     public function supplier()
     {
         // Optional: direct link if needed; eager load nested via product.supplier in queries
         return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
     }
}
