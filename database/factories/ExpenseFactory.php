<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_name' => Str::limit($this->faker->words(3, true), 45, ''),
            'category' => $this->faker->randomElement(Expense::CATEGORIES),
            'expense_amount' => $this->faker->randomFloat(2, 20, 800),
            'expense_date' => now()->toDateString(),
            'notes' => $this->faker->sentence(),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }
}
