<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MoonshineUserSeeder::class,
            ProductSeeder::class,
            StockMovementSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
