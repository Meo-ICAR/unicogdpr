<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ExternalProcessor;
use App\Models\ExternalProcessorAudit;
use App\Models\User;
use App\Notifications\DpoAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VendorAuditQuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    private function audit(): ExternalProcessorAudit
    {
        $processor = ExternalProcessor::create([
            'company_id' => Company::factory()->create()->id,
            'name' => 'Fornitore di Test S.r.l.',
            'email' => 'privacy@fornitoretest.it',
        ]);

        return ExternalProcessorAudit::create([
            'external_processor_id' => $processor->id,
            'title' => 'Audit Annuale 2026',
            'audit_date' => now(),
            'status' => 'pending_answers',
            'token' => 'test-token-1234567890',
        ]);
    }

    public function test_public_form_is_reachable_via_token(): void
    {
        $audit = $this->audit();

        $this->get(route('vendor-audit.show', $audit->token))
            ->assertOk()
            ->assertSee('Audit Annuale 2026');
    }

    public function test_submitting_the_questionnaire_marks_it_as_answered_and_notifies_dpo(): void
    {
        Notification::fake();

        $audit = $this->audit();
        $company = $audit->externalProcessor->company;
        $dpo = User::factory()->create();
        $company->users()->attach($dpo, ['role' => 'dpo']);

        $this->post(route('vendor-audit.submit', $audit->token), [
            'vendor_answers' => 'Adottiamo cifratura AES-256 e controllo accessi basato su ruoli.',
        ])->assertOk();

        $audit->refresh();

        $this->assertTrue($audit->isSubmitted());
        $this->assertSame('under_review', $audit->status);
        $this->assertSame('Adottiamo cifratura AES-256 e controllo accessi basato su ruoli.', $audit->vendor_answers);

        Notification::assertSentTo($dpo, DpoAlert::class);
    }

    public function test_already_submitted_questionnaire_cannot_be_reopened(): void
    {
        $audit = $this->audit();
        $audit->update(['submitted_at' => now()]);

        $this->get(route('vendor-audit.show', $audit->token))->assertStatus(410);
        $this->post(route('vendor-audit.submit', $audit->token), [
            'vendor_answers' => 'Tentativo di reinvio',
        ])->assertStatus(410);
    }
}
