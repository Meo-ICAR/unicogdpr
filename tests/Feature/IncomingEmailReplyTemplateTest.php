<?php

namespace Tests\Feature;

use App\Filament\Resources\IncomingEmails\Pages\ViewIncomingEmail;
use App\Mail\InboxReplyMail;
use App\Models\Company;
use App\Models\EmailTemplate;
use App\Models\IncomingEmail;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class IncomingEmailReplyTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function makeTemplate(): EmailTemplate
    {
        return EmailTemplate::create([
            'code' => 'identity_verification_request',
            'name' => 'Richiesta di identificazione del mittente',
            'subject' => '[{company_name}] Richiesta di identificazione',
            'body_html' => '<p>Gentile {requester_name}, la preghiamo di identificarsi.</p>',
            'placeholders' => ['{requester_name}', '{company_name}'],
            'is_active' => true,
        ]);
    }

    public function test_template_prefills_subject_and_body_and_the_edited_text_is_sent(): void
    {
        Mail::fake();

        $company = Company::factory()->create(['name' => 'Azienda Test']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $email = IncomingEmail::factory()->for($company)->create([
            'from_name' => 'Mario Rossi',
            'from_email' => 'mario@example.com',
            'subject' => 'Richiesta dati',
        ]);
        $template = $this->makeTemplate();

        $this->actingAs($user);

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        Filament::setTenant($company);

        Livewire::test(ViewIncomingEmail::class, ['record' => $email->id])
            ->mountAction('reply')
            ->assertSchemaStateSet(['subject' => 'Re: Richiesta dati'])
            ->fillForm(['email_template_id' => $template->id])
            ->assertSchemaStateSet(function (array $state): array {
                $this->assertStringContainsString('Gentile Mario Rossi', json_encode($state['body_html']));

                return [];
            })
            ->setActionData([
                'email_template_id' => $template->id,
                'subject' => 'Re: Richiesta dati (modificato)',
                'body_html' => '<p>Testo modificato dall operatore</p>',
            ])
            ->callMountedAction()
            ->assertNotified('Risposta inviata');

        Mail::assertSent(InboxReplyMail::class, fn (InboxReplyMail $mail): bool => $mail->hasTo('mario@example.com')
            && $mail->renderedSubject === 'Re: Richiesta dati (modificato)'
            && str_contains($mail->renderedBodyHtml, 'Testo modificato dall operatore'));
    }
}
