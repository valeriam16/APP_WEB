<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            'name' => "Iphone 13",
            'description' => "Mobile phone Apple",
            'amount' => 980
        ]);
        DB::table('products')->insert([
            'name' => "Samsung Galaxy S21",
            'description' => "Mobile phone Samsung",
            'amount' => 800
        ]);
        DB::table('products')->insert([
            'name' => "Xiaomi Mi 11",
            'description' => "Mobile phone Xiaomi",
            'amount' => 600
        ]);
    }
}
