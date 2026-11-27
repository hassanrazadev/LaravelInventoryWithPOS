<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subTotal = $this->faker->randomFloat(2, 50, 500);
        $tax = round($subTotal * 0.05, 2);
        $total = round($subTotal + $tax, 2);

        return [
            'customer_id' => Customer::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
            'order_no' => Order::generateOrderNumber(),
            'total_items' => $this->faker->numberBetween(1, 5),
            'sub_total' => $subTotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'cash',
            'paid_amount' => $total,
            'change_amount' => 0,
            'status' => Order::STATUS_COMPLETED,
            'notes' => null,
        ];
    }
}
