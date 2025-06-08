<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rolador>
 */
class RoladorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'photo' => $this->faker->imageUrl(640, 480, 'Rolador', true),
            'weekly_payment' => $this->faker->randomFloat(2, 100, 1000),
            'activity_description' => $this->faker->text(200),
            'category_id' => Category::factory()
        ];
    }
}
