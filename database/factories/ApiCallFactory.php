<?php

namespace Database\Factories;

use App\Models\ApiCall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiCall>
 */
class ApiCallFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service' => 'mediafacile',
            'operation' => 'pratiche',
            'method' => 'GET',
            'url' => fake()->url(),
            'status' => 200,
            'successful' => true,
            'duration_ms' => fake()->numberBetween(20, 2000),
            'response_bytes' => fake()->numberBetween(100, 100000),
            'called_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => 500, 'successful' => false, 'error' => 'HTTP 500']);
    }
}
