<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = ucfirst($this->faker->unique()->words(2, true));

        return [
            'created_by' => User::factory(),
            'updated_by' => null,
            'parent_id' => null,
            'category_name' => $name,
            'category_code' => strtoupper($this->faker->unique()->bothify('CAT-####')),
            'category_image' => 'category/'.Str::random(10).'.png',
            'category_slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 9999),
        ];
    }
}
