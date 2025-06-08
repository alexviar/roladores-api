<?php

namespace Database\Factories;

use App\Models\Rolador;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RentalPeriod>
 */
class RentalPeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Por defecto, crear periodos de alquiler en el pasado
        $startDate = fake()->dateTimeBetween('-2 years', '-1 day');
        $endDate = Date::parse($startDate)->addWeek();
        $paymentDate = fake()->optional(0.7)->dateTimeBetween($startDate, $endDate);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'payment_date' => $paymentDate,
            'amount_due' => fake()->randomFloat(2, 100, 5000),
            'rolador_id' => Rolador::factory()
        ];
    }

    /**
     * Configure current rental period
     */
    public function current(): self
    {
        return $this->state(function () {
            $startDate = fake()->dateTimeBetween('-1 weeks', 'now');
            $endDate = Date::parse($startDate)->addWeek();
            $paymentDate = fake()->optional(0.7)->dateTimeBetween($startDate, $endDate);

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_date' => $paymentDate,
            ];
        });
    }

    /**
     * Configure unpaid rental period
     */
    public function unpaid(): self
    {
        return $this->state(function () {
            return [
                'payment_date' => null,
            ];
        });
    }


    /**
     * Configure paid rental period
     */
    public function paid(): self
    {
        return $this->state(function ($attributes) {
            $paymentDate = fake()->dateTimeBetween($attributes['start_date'], $attributes['end_date']);
            return [
                'payment_date' => $paymentDate,
            ];
        });
    }


    /**
     * Configure overdue rental period
     */
    public function overdue(): self
    {
        return $this->state(function () {
            $startDate = fake()->dateTimeBetween('-2 years', '-1 week');
            $startDate = Date::parse($startDate)->subDay();
            $endDate = Date::parse($startDate)->addWeek();

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_date' => null,
            ];
        });
    }
}
