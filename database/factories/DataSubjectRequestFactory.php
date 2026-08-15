<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DataSubjectRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataSubjectRequest>
 */
class DataSubjectRequestFactory extends Factory
{
    protected $model = DataSubjectRequest::class;

    public function definition(): array
    {
        $receivedAt = fake()->dateTimeBetween('-20 days', 'now');
        $types = ['access', 'rectification', 'erasure', 'restriction', 'portability', 'objection', 'withdraw_consent'];
        $statuses = ['pending', 'in_progress', 'completed'];
        $channels = ['email', 'pec', 'paper', 'web_form'];

        return [
            'company_id'                  => Company::factory(),
            'requester_name'              => fake()->name(),
            'requester_email'             => fake()->safeEmail(),
            'requester_phone'             => fake()->phoneNumber(),
            'request_type'                => fake()->randomElement($types),
            'status'                      => fake()->randomElement($statuses),
            'received_at'                 => $receivedAt,
            'deadline_at'                 => (clone $receivedAt)->modify('+30 days'),
            'request_description'         => fake()->paragraph(2),
            'response_notes'              => fake()->optional(0.3)->paragraph(1),
            'identity_verified'           => fake()->boolean(70),
            'identity_verification_method'=> 'Carta d\'Identità Elettronica (CIE)',
            'channel'                     => fake()->randomElement($channels),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'pending',
            'received_at' => now()->subDays(26),
            'deadline_at' => now()->addDays(4),
        ]);
    }
}
