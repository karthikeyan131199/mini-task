<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('PRD-####')),
            'price_per_unit' => fake()->randomFloat(2, 5, 200),
            'tax_percentage' => fake()->randomElement([0.00, 5.00, 12.00, 18.00]),
            'stock_on_hand' => fake()->numberBetween(1, 50),
        ];
    }
}
