<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SaleReturn>
 */
class SaleReturnFactory extends Factory
{
    protected $model = SaleReturn::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => 1,
            'return_price' => 25.00,
            'reason' => $this->faker->sentence(),
            'restock' => true,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
