<?php

namespace Database\Seeders;

use App\Models\Farmer;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    public function run()
    {
        Farmer::create([
            'name' => 'John Doe',
            'village' => 'Greenville',
            'state' => 'California',
            'district' => 'Orange',
            'city' => 'Los Angeles',
            'pincode' => '90001',
            'no_animals' => 5,
            'phone' => '1234567890',
            'date' => now(),
            'is_active' => 1,
            'cod' => 1,
            'qty_discount' => 10.50,
        ]);
    }
}