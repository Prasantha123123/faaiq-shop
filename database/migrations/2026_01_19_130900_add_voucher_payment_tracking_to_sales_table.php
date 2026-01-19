<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('paid_with_voucher')->default(false)->after('has_vouchers');
            $table->unsignedBigInteger('redeemed_voucher_id')->nullable()->after('paid_with_voucher');
            $table->decimal('voucher_payment_amount', 10, 2)->default(0)->after('redeemed_voucher_id');
            
            $table->foreign('redeemed_voucher_id')->references('id')->on('vouchers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['redeemed_voucher_id']);
            $table->dropColumn(['paid_with_voucher', 'redeemed_voucher_id', 'voucher_payment_amount']);
        });
    }
};
