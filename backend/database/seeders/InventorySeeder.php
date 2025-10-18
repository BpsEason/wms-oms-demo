<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventory')->insert([
            [
                'sku' => 'SKU-001',
                'product_name' => '智慧型手機',
                'quantity' => 100,
                'location' => 'A1-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-002',
                'product_name' => '藍牙耳機',
                'quantity' => 250,
                'location' => 'A1-02-05',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'SKU-003',
                'product_name' => '筆記型電腦',
                'quantity' => 50,
                'location' => 'B2-01-03',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
