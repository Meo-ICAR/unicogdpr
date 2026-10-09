<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DataSubjectRequest;
use App\Models\IncomingEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncomingEmailAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('private');
    }

    public function test_attachment_is_shown_inline_or_downloaded(): void
    {
        $email = IncomingEmail::factory()->create();
        $media = $email->addMediaFromString('%PDF-1.4 test')
            ->usingFileName('istanza.pdf')
            ->toMediaCollection('email_attachments');

        $this->actingAs(User::factory()->create());

        $this->get(route('incoming-email.attachment', $media))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename=istanza.pdf');

        $this->get(route('incoming-email.attachment', [$media, 'download' => 1]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=istanza.pdf');
    }

    public function test_guests_cannot_open_attachments(): void
    {
        $email = IncomingEmail::factory()->create();
        $media = $email->addMediaFromString('contenuto')
            ->usingFileName('nota.txt')
            ->toMediaCollection('email_attachments');

        $this->get(route('incoming-email.attachment', $media))->assertRedirect();
    }

    public function test_media_not_belonging_to_an_email_attachment_is_not_found(): void
    {
        $dsar = DataSubjectRequest::create([
            'company_id' => Company::factory()->create()->id,
            'requester_name' => 'Richiedente Test',
            'requester_email' => 'richiedente@example.com',
            'request_type' => 'access',
            'status' => 'received',
            'received_at' => now(),
            'deadline_at' => now()->addDays(30),
        ]);
        $media = $dsar->addMediaFromString('contenuto')
            ->usingFileName('dsar.txt')
            ->toMediaCollection('dsar_attachments');

        $this->actingAs(User::factory()->create());

        $this->get(route('incoming-email.attachment', $media))->assertNotFound();
    }

    public function test_email_page_shows_thread_and_attachment_links(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $first = IncomingEmail::factory()->for($company)->create([
            'subject' => 'Invio istanza GDPR',
            'thread_id' => 'thread-1',
            'received_at' => now()->subDay(),
        ]);
        $reply = IncomingEmail::factory()->for($company)->create([
            'subject' => 'R: Invio istanza GDPR',
            'thread_id' => 'thread-1',
            'received_at' => now(),
        ]);
        $media = $first->addMediaFromString('%PDF-1.4 test')
            ->usingFileName('istanza.pdf')
            ->toMediaCollection('email_attachments');

        $this->actingAs($user)
            ->get(route('filament.admin.resources.incoming-emails.view', ['tenant' => $company->id, 'record' => $first->id]))
            ->assertOk()
            ->assertSee('R: Invio istanza GDPR')
            ->assertSee('istanza.pdf')
            ->assertSee(route('incoming-email.attachment', $media), false)
            ->assertSee('Scarica');
    }

    public function test_email_page_renders_html_body_safely(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $email = IncomingEmail::factory()->for($company)->create([
            'body_text' => 'testo alternativo',
            'body_html' => '<html><head><title>Carport</title></head><body><h2>Offerta fotovoltaico</h2>'
                .'<p>Messaggio con <strong>grassetto</strong></p>'
                .'<img src="https://tracker.example.com/pixel.gif">'
                .'<script>alert(1)</script></body></html>',
        ]);

        $this->actingAs($user)
            ->get(route('filament.admin.resources.incoming-emails.view', ['tenant' => $company->id, 'record' => $email->id]))
            ->assertOk()
            ->assertSee('Offerta fotovoltaico')
            ->assertSee('<strong>grassetto</strong>', false)
            ->assertDontSee('tracker.example.com')
            ->assertDontSee('alert(1)', false)
            ->assertDontSee('testo alternativo');
    }
}
