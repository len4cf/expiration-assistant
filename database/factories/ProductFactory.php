<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Milk', 'Yogurt', 'Cheese', 'Eggs', 'Bread', 'Ham', 'Butter']),
            'quantity' => fake()->numberBetween(1, 5),
            'expiration_date' => fake()->dateTimeBetween('now', '+30 days'),
            'opened_at' => null,
            'location' => fake()->randomElement(['fridge', 'freezer', 'pantry']),
            'status' => ProductStatus::Active,
        ];
    }

    /**
     * Indicate that the product has been consumed.
     */
    public function consumed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Consumed,
            'finished_at' => now(),
        ]);
    }

    /**
     * Indicate that the product has been discarded.
     */
    public function discarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductStatus::Discarded,
            'finished_at' => now(),
        ]);
    }
}
