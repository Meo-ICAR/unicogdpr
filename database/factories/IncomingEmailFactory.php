<?php

namespace Database\Factories;

use App\Enums\EmailClassification;
use App\Models\Company;
use App\Models\IncomingEmail;
use App\Models\MailAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IncomingEmail>
 */
class IncomingEmailFactory extends Factory
{
    protected $model = IncomingEmail::class;

    public function definition(): array
    {
        $messageId = fake()->uuid().'@mail.example.com';
        $from = fake()->safeEmail();

        return [
            'company_id' => Company::factory(),
            'mail_account_id' => MailAccount::factory(),
            'message_id' => $messageId,
            'in_reply_to' => null,
            'references' => null,
            'thread_id' => $messageId,
            'from_email' => $from,
            'from_name' => fake()->name(),
            'to' => [['email' => 'dpo@azienda.it', 'name' => 'DPO']],
            'cc' => null,
            'subject' => fake()->sentence(),
            'body_text' => fake()->paragraph(),
            'body_html' => null,
            'received_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'is_read' => false,
            'classification' => EmailClassification::Other,
            'data_subject_request_id' => null,
        ];
    }

    public function unread(): static
    {
        return $this->state(fn () => ['is_read' => false]);
    }

    public function classifiedAs(EmailClassification $classification): static
    {
        return $this->state(fn () => ['classification' => $classification]);
    }
}
