<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = StoreSetting::current();

        if (! $settings->wasRecentlyCreated) {
            return;
        }

        $settings->update([
            'store_name' => 'My Store',
            'currency_code' => 'LKR',
            'currency_symbol' => 'Rs.',
            'default_tax_rate' => 0,
            'receipt_footer_text' => 'Thank you for your business!',
        ]);
    }
}
