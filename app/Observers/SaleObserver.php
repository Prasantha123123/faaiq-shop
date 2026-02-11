<?php

namespace App\Observers;

use App\Models\Sale;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     * Generate order_id based on the auto-increment ID after the record is created.
     */
    public function created(Sale $sale): void
    {
        // Generate order_id using the format: HB/YY/XXX
        // Where YY is the 2-digit year and XXX is the sale ID padded to 3 digits
        $year = date('y');
        $sale->order_id = "HB/{$year}/" . str_pad($sale->id, 3, '0', STR_PAD_LEFT);
        
        // Save without triggering events again
        $sale->saveQuietly();
    }
}
