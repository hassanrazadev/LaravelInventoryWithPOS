<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_by' => User::factory(),
            'updated_by' => null,
            'name' => Str::limit($this->faker->company(), 47, ''),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('03#########'),
            'image' => null,
            'address' => $this->faker->address(),
        ];
    }
}
