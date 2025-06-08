<?php

namespace Database\Factories;

use App\Models\Rolador;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Punishment>
 */
class PunishmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Por defecto, crear castigos en el pasado
        $numWeeks = rand(1, 3);
        $startDate = fake()->dateTimeBetween('-2 years', '-1 day');
        $endDate = Date::parse($startDate)->addWeeks($numWeeks);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => fake()->sentence(),
            'rolador_id' => Rolador::factory()
        ];
    }

    /**
     * Configura el castigo para que termine en el futuro
     */
    public function current(mixed $minDate): self
    {
        return $this->state(function () use ($minDate) {
            $numWeeks = rand(1, 3);
            $startDate = fake()->dateTimeBetween("-{$numWeeks} weeks", 'now');
            if (!Date::parse($minDate)->isBefore($startDate)) {
                $startDate = $minDate;
            }
            $endDate = Date::parse($startDate)->addWeeks($numWeeks);

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        });
    }
}
