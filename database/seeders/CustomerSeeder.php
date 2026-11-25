<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'Thomas Mathew', 'email' => 'thomas@example.com'],
            ['name' => 'Ananya Sharma', 'email' => 'ananya@example.com'],
            ['name' => 'Rajesh Kumar', 'email' => 'rajesh@example.com'],
            ['name' => 'Priya Patel', 'email' => 'priya@example.com'],
        ];

        foreach ($customers as $c) {
            Customer::updateOrCreate(['email' => $c['email']], $c);
        }

        // Generate additional mock customers using the CustomerFactory
        Customer::factory()->count(6)->create();
    }
}
