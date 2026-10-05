<?php

namespace Tests\Feature;

use App\Mail\InboxReplyMail;
use App\Models\MailAccount;
use App\Services\Mail\OutgoingMailerFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class MailAccountSmtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_smtp_configured_requires_host_and_password_on_password_auth(): void
    {
        $account = MailAccount::factory()->make(['smtp_host' => null, 'smtp_password' => null]);
        $this->assertFalse($account->hasSmtpConfigured());

        $account = MailAccount::factory()->make(['smtp_host' => 'smtp.gmail.com', 'smtp_password' => 'app-password']);
        $this->assertTrue($account->hasSmtpConfigured());
    }

    public function test_oauth_account_never_reports_smtp_configured(): void
    {
        $account = MailAccount::factory()->oauth()->make([
            'smtp_host' => 'smtp.gmail.com',
            'smtp_password' => 'irrelevant',
        ]);

        $this->assertFalse($account->hasSmtpConfigured());
    }

    public function test_sends_mailable_from_the_account_own_address(): void
    {
        Mail::fake();

        $account = MailAccount::factory()->create([
            'email_address' => 'privacy@digitalrevgroup.com',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'privacy@digitalrevgroup.com',
            'smtp_password' => 'app-password',
        ]);

        $mailable = new InboxReplyMail(
            renderedSubject: 'Re: Test',
            renderedBodyHtml: '<p>Ciao</p>',
        );

        (new OutgoingMailerFactory)->send($account, 'destinatario@example.com', $mailable);

        Mail::assertSent(InboxReplyMail::class, function (InboxReplyMail $mail) {
            return $mail->hasTo('destinatario@example.com')
                && collect($mail->from)->contains(fn (array $address) => $address['address'] === 'privacy@digitalrevgroup.com');
        });

        $this->assertSame('smtp', config('mail.mailers.mail_account_'.$account->id.'.transport'));
        $this->assertSame('smtp.gmail.com', config('mail.mailers.mail_account_'.$account->id.'.host'));
    }

    public function test_throws_when_smtp_not_configured(): void
    {
        $account = MailAccount::factory()->create(['smtp_host' => null, 'smtp_password' => null]);

        $this->expectException(RuntimeException::class);

        (new OutgoingMailerFactory)->send(
            $account,
            'destinatario@example.com',
            new InboxReplyMail(renderedSubject: 'Re: Test', renderedBodyHtml: '<p>Ciao</p>'),
        );
    }
}
