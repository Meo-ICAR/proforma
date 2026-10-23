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
            'provider' => 'mediafacile',
            'operation' => 'pratiche',
            'status' => ApiCall::STATUS_SUCCESS,
            'http_status' => 200,
            'duration_ms' => fake()->numberBetween(20, 2000),
            'request' => ['method' => 'GET', 'url' => fake()->url()],
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => ApiCall::STATUS_FAILED, 'http_status' => 500, 'error' => 'HTTP 500']);
    }
}
