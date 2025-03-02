<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-2 years'); // Ensure created_at is in the past
        $updatedAt = fake()->dateTimeBetween($createdAt, 'now'); // Ensure updated_at is later

        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt
        ];
    }

}
