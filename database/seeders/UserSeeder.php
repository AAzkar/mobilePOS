<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Default PINs are for local/demo use only — change them before going live.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['name' => 'Admin'],
            [
                'pin_hash' => Hash::make('1234'),
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['name' => 'Cashier'],
            [
                'pin_hash' => Hash::make('1111'),
                'role' => 'cashier',
                'is_active' => true,
            ],
        );
    }
}
