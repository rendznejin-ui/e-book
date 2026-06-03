<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999),
            'author' => fake()->name(),
            'isbn' => null,
            'description' => fake()->paragraph(),
            'price_cents' => fake()->numberBetween(500, 5000),
            'stock_qty' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(['stock_qty' => 0]);
    }
}
