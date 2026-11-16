<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Layanan Uji ??-##'),
            'current_price' => fake()->numberBetween(10000, 100000),
        ];
    }
}
