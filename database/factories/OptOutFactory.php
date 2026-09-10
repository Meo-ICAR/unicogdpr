<?php

namespace Database\Factories;

use App\Models\OptOut;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OptOut>
 */
class OptOutFactory extends Factory
{
    protected $model = OptOut::class;

    public function definition(): array
    {
        return [
            'phone' => fake()->boolean(80) ? fake()->numerify('+39 3## #######') : null,
            'email' => fake()->boolean(70) ? fake()->safeEmail() : null,
            'fiscal_code' => strtoupper(fake()->bothify('??????##?##?###?')),
            'channel' => fake()->randomElement(['all', 'phone', 'email', 'sms']),
            'source' => fake()->randomElement(['direct_request', 'rpo', 'client_request', 'dsar']),
            'client_controller_id' => null,
            'opt_out_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function global(): static
    {
        return $this->state(fn () => ['channel' => 'all', 'client_controller_id' => null]);
    }
}
