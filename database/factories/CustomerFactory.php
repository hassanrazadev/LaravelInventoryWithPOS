<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'updated_by' => null,
            'name' => Str::limit($this->faker->name(), 47, ''),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('03#########'),
            'image' => null,
            'address' => $this->faker->address(),
        ];
    }
}
