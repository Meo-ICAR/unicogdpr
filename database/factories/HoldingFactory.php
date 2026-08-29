<?php

namespace Database\Factories;

use App\Models\Holding;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Holding>
 */
class HoldingFactory extends Factory
{
    protected $model = Holding::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Group S.p.A.',
            'vat_number' => fake()->numerify('IT###########'),
            'tax_code' => fake()->numerify('###########'),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'email' => fake()->companyEmail(),
            'pec' => fake()->userName().'@pec.it',
            'phone' => fake()->phoneNumber(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
