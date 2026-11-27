<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = ucfirst($this->faker->unique()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'created_by' => User::factory(),
            'updated_by' => null,
            'product_name' => $name,
            'product_code' => strtoupper($this->faker->unique()->bothify('PRD-#####')),
            'quantity' => 0,
            'sale_price' => $this->faker->randomFloat(2, 10, 500),
            'product_image' => 'product/' . Str::random(10) . '.png',
            'product_slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 9999),
        ];
    }
}
