<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VoucherCategory;

class VoucherCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => '1000',
                'amount' => 1000.00,
                'description' => 'Rs 1000 Gift Voucher',
                'is_active' => true,
            ],
            [
                'name' => '3000',
                'amount' => 3000.00,
                'description' => 'Rs 3000 Gift Voucher',
                'is_active' => true,
            ],
            [
                'name' => '5000',
                'amount' => 5000.00,
                'description' => 'Rs 5000 Gift Voucher',
                'is_active' => true,
            ],
            [
                'name' => '10000',
                'amount' => 10000.00,
                'description' => 'Rs 10000 Gift Voucher',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            VoucherCategory::create($category);
        }
    }
}
