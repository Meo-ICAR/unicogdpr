<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\MailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailAccount>
 */
class MailAccountFactory extends Factory
{
    protected $model = MailAccount::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => 'Casella DPO',
            'type' => 'email',
            'email_address' => fake()->safeEmail(),
            'auth_type' => 'password',
            'provider' => null,
            'imap_host' => 'imap.example.com',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'imap_username' => fake()->safeEmail(),
            'imap_password' => 'secret-app-password',
            'is_active' => true,
        ];
    }

    public function bounce(): static
    {
        return $this->state(fn () => ['type' => 'bounce', 'name' => 'Casella Bounce']);
    }

    public function oauth(string $provider = 'google'): static
    {
        return $this->state(fn () => [
            'auth_type' => 'oauth2',
            'provider' => $provider,
            'imap_password' => null,
            'access_token' => 'old-access-token',
            'refresh_token' => 'stored-refresh-token',
            'token_expires_at' => now()->subHour(),
        ]);
    }
}
