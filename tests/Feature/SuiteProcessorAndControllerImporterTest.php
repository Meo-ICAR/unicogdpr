<?php

namespace Tests\Feature;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\ExternalProcessor;
use App\Models\Suite\SuiteCompany;
use App\Services\SuiteImport\ClientControllerImporter;
use App\Services\SuiteImport\ExternalProcessorImporter;
use App\Services\SuiteImport\ImportReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class SuiteProcessorAndControllerImporterTest extends TestCase
{
    use RefreshDatabase;
    use SuiteFixtures;

    private Company $local;

    private SuiteCompany $suite;

    private int $suiteCompanyId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();

        $this->suiteCompanyId = $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'vat_number' => '10282211001']);
        $this->suite = SuiteCompany::query()->findOrFail($this->suiteCompanyId);
        $this->local = Company::factory()->create(['vat_number' => '10282211001']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function suiteAgent(array $overrides = []): int
    {
        return $this->suiteInsert('fornitoris', array_merge([
            'company_id' => $this->suiteCompanyId,
            'name' => 'Agenzia Alfa',
            'piva' => '01234567890',
            'is_active' => 1,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function suiteBank(array $overrides = []): int
    {
        return $this->suiteInsert('clientis', array_merge([
            'company_id' => $this->suiteCompanyId,
            'name' => 'Banca Beta',
            'piva' => '09876543210',
            'is_active' => 1,
            'is_dummy' => 0,
            'status' => 'ATTIVO',
        ], $overrides));
    }

    private function importAgents(): ImportReport
    {
        return (new ExternalProcessorImporter)->import($this->local, $this->suite);
    }

    private function importBanks(): ImportReport
    {
        return (new ClientControllerImporter)->import($this->local, $this->suite);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function localProcessor(array $attributes = []): ExternalProcessor
    {
        return ExternalProcessor::query()->create(array_merge([
            'company_id' => $this->local->id,
            'name' => 'Manuale',
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function localController(array $attributes = []): ClientController
    {
        return ClientController::query()->create(array_merge([
            'company_id' => $this->local->id,
            'name' => 'Manuale',
            'is_active' => true,
        ], $attributes));
    }

    public function test_imports_agents_as_external_processors(): void
    {
        $id = $this->suiteAgent([
            'email' => 'info@alfa.test',
            'pec' => 'alfa@pec.test',
            'tel' => '061234',
            'indirizzo' => 'Via Roma 1',
            'cap' => '00100',
            'comune' => 'Roma',
            'prov' => '',
            'stipulated_at' => '2024-03-05 00:00:00',
        ]);

        $report = $this->importAgents();

        $this->assertSame(1, $report->created);

        $processor = ExternalProcessor::query()->where('source_id', (string) $id)->firstOrFail();
        $this->assertSame('unicosuite:fornitoris', $processor->source_system);
        $this->assertSame($this->local->id, $processor->company_id);
        $this->assertSame('Agenzia Alfa', $processor->name);
        $this->assertSame('01234567890', $processor->vat_number);
        $this->assertSame('info@alfa.test', $processor->email);
        $this->assertSame('alfa@pec.test', $processor->pec);
        $this->assertSame('061234', $processor->phone);
        $this->assertSame('Via Roma 1 00100 Roma', $processor->address);
        $this->assertSame('2024-03-05', $processor->contract_date->toDateString());
        $this->assertTrue($processor->is_active);
    }

    public function test_agent_with_only_cf_uses_it_as_vat_number(): void
    {
        $this->suiteAgent(['piva' => null, 'cf' => 'RSSMRA80A01H501U']);

        $this->importAgents();

        $this->assertSame('RSSMRA80A01H501U', ExternalProcessor::query()->firstOrFail()->vat_number);
    }

    public function test_agent_without_piva_and_cf_is_skipped_with_warning(): void
    {
        $id = $this->suiteAgent(['name' => 'Persona Riservata', 'piva' => '', 'cf' => null]);
        $blankId = $this->suiteAgent(['name' => '  ', 'piva' => '111']);
        $this->suiteAgent(['name' => 'Cancellato', 'piva' => '222', 'deleted_at' => now()]);

        $report = $this->importAgents();

        $this->assertSame(0, ExternalProcessor::query()->count());
        $this->assertCount(2, $report->warnings);
        $this->assertStringContainsString((string) $id, $report->warnings[0]);
        $this->assertStringNotContainsString('Persona Riservata', $report->warnings[0]);
        $this->assertStringContainsString((string) $blankId, $report->warnings[1]);
    }

    public function test_agent_keeps_local_privacy_fields_on_update(): void
    {
        $id = $this->suiteAgent(['email' => 'uno@alfa.test']);
        $this->importAgents();

        ExternalProcessor::query()->where('source_id', (string) $id)->update([
            'has_dpa_signed' => true,
            'dpa_signed_at' => '2024-01-01',
            'processing_description' => 'Hosting',
            'notes' => 'nota',
            'dpo_contact' => 'dpo@alfa.test',
            'general_authorization_granted' => true,
        ]);

        DB::connection('unicosuite')->table('fornitoris')->where('id', $id)->update(['email' => 'due@alfa.test']);

        $report = $this->importAgents();

        $this->assertSame(1, $report->updated);
        $processor = ExternalProcessor::query()->firstOrFail();
        $this->assertSame('due@alfa.test', $processor->email);
        $this->assertTrue($processor->has_dpa_signed);
        $this->assertSame('2024-01-01', $processor->dpa_signed_at->toDateString());
        $this->assertSame('Hosting', $processor->processing_description);
        $this->assertSame('nota', $processor->notes);
        $this->assertSame('dpo@alfa.test', $processor->dpo_contact);
        $this->assertTrue($processor->general_authorization_granted);
    }

    public function test_agent_adopts_existing_local_row_by_vat_number_without_losing_values(): void
    {
        $manual = $this->localProcessor(['name' => 'Alfa locale', 'vat_number' => '01234567890', 'pec' => 'manuale@pec.test', 'has_dpa_signed' => true]);
        $other = $this->localProcessor(['name' => 'Altra azienda', 'vat_number' => '99999999999']);
        $id = $this->suiteAgent();

        $report = $this->importAgents();

        $this->assertSame(1, $report->updated);
        $this->assertSame(0, $report->created);
        $this->assertSame(2, ExternalProcessor::query()->count());

        $manual->refresh();
        $this->assertSame((string) $id, $manual->source_id);
        $this->assertSame('Agenzia Alfa', $manual->name);
        $this->assertSame('manuale@pec.test', $manual->pec);
        $this->assertTrue($manual->has_dpa_signed);
        $this->assertNull($other->refresh()->source_system);
    }

    public function test_adoption_ignores_empty_keys_and_other_companies(): void
    {
        $this->localProcessor(['vat_number' => '']);
        $this->localProcessor(['vat_number' => null]);
        $foreign = ExternalProcessor::query()->create([
            'company_id' => Company::factory()->create()->id,
            'name' => 'Altra societa',
            'vat_number' => '01234567890',
            'is_active' => true,
        ]);
        $this->suiteAgent();

        $report = $this->importAgents();

        $this->assertSame(1, $report->created);
        $this->assertNull($foreign->refresh()->source_system);
        $this->assertSame(3, ExternalProcessor::query()->whereNull('source_system')->count());
        $this->assertSame(1, ExternalProcessor::query()->whereNotNull('source_system')->where('company_id', $this->local->id)->count());
    }

    public function test_two_agents_sharing_the_same_piva_second_is_skipped(): void
    {
        $first = $this->suiteAgent(['name' => 'Primo']);
        $second = $this->suiteAgent(['name' => 'Secondo']);

        $report = $this->importAgents();

        $this->assertSame(1, $report->created);
        $this->assertCount(1, $report->warnings);
        $this->assertStringContainsString((string) $second, $report->warnings[0]);
        $this->assertSame(1, ExternalProcessor::query()->count());
        $this->assertSame((string) $first, ExternalProcessor::query()->firstOrFail()->source_id);
    }

    public function test_locally_deleted_agent_is_skipped_with_warning(): void
    {
        $id = $this->suiteAgent();
        $this->importAgents();
        ExternalProcessor::query()->firstOrFail()->delete();

        $report = $this->importAgents();

        $this->assertSame(0, $report->created);
        $this->assertCount(1, $report->warnings);
        $this->assertStringContainsString((string) $id, $report->warnings[0]);
        $this->assertSame(1, ExternalProcessor::withTrashed()->count());
    }

    public function test_imports_banks_as_client_controllers_and_skips_dummy(): void
    {
        $id = $this->suiteBank([
            'email' => 'info@beta.test',
            'dpo_email' => 'dpo@beta.test',
            'privacy_contact_email' => 'privacy@beta.test',
            'stipulated_at' => '2023-12-31',
        ]);
        $dummy = $this->suiteBank(['name' => 'Fittizia', 'piva' => '555', 'is_dummy' => 1]);
        $noVat = $this->suiteBank(['name' => 'Senza Piva', 'piva' => '']);
        $this->suiteBank(['name' => 'Cancellata', 'piva' => '777', 'deleted_at' => now()]);

        $report = $this->importBanks();

        $this->assertSame(1, $report->created);
        $this->assertCount(2, $report->warnings);
        $this->assertStringContainsString((string) $dummy, $report->warnings[0]);
        $this->assertStringContainsString((string) $noVat, $report->warnings[1]);

        $bank = ClientController::query()->firstOrFail();
        $this->assertSame((string) $id, $bank->source_id);
        $this->assertSame('unicosuite:clientis', $bank->source_system);
        $this->assertSame('Banca Beta', $bank->name);
        $this->assertSame('09876543210', $bank->vat_number);
        $this->assertSame('info@beta.test', $bank->email);
        $this->assertSame('dpo@beta.test', $bank->dpo_contact);
        $this->assertSame('2023-12-31', $bank->agreement_date->toDateString());
        $this->assertTrue($bank->is_active);
    }

    public function test_bank_dpo_contact_falls_back_and_is_never_overwritten(): void
    {
        $id = $this->suiteBank(['dpo_email' => null, 'privacy_contact_email' => 'privacy@beta.test']);
        $this->importBanks();

        $bank = ClientController::query()->firstOrFail();
        $this->assertSame('privacy@beta.test', $bank->dpo_contact);

        $bank->update(['dpo_contact' => 'locale@beta.test', 'notes' => 'nota', 'agreement_description' => 'accordo']);
        DB::connection('unicosuite')->table('clientis')->where('id', $id)->update(['dpo_email' => 'nuovo@beta.test', 'name' => 'Banca Rinominata']);

        $report = $this->importBanks();

        $this->assertSame(1, $report->updated);
        $bank->refresh();
        $this->assertSame('Banca Rinominata', $bank->name);
        $this->assertSame('locale@beta.test', $bank->dpo_contact);
        $this->assertSame('nota', $bank->notes);
        $this->assertSame('accordo', $bank->agreement_description);
    }

    public function test_bank_with_expired_or_withdrawn_status_is_inactive(): void
    {
        $this->suiteBank(['piva' => '1', 'status' => 'SCADUTO']);
        $this->suiteBank(['piva' => '2', 'status' => 'RECEDUTO']);
        $this->suiteBank(['piva' => '3', 'status' => 'SOSPESO']);
        $this->suiteBank(['piva' => '4', 'is_active' => 0]);

        $this->importBanks();

        $active = ClientController::query()->orderBy('vat_number')->pluck('is_active', 'vat_number')->all();
        $this->assertSame([false, false, true, false], array_values(array_map('boolval', $active)));
    }

    public function test_bank_adopts_existing_local_row_by_vat_number(): void
    {
        $manual = $this->localController(['name' => 'Beta locale', 'vat_number' => '09876543210', 'notes' => 'nota']);
        $id = $this->suiteBank();

        $report = $this->importBanks();

        $this->assertSame(1, $report->updated);
        $this->assertSame(1, ClientController::query()->count());
        $manual->refresh();
        $this->assertSame((string) $id, $manual->source_id);
        $this->assertSame('nota', $manual->notes);
    }

    public function test_both_are_idempotent_and_deactivate_removed(): void
    {
        $this->suiteAgent(['piva' => 'A1']);
        $removedAgent = $this->suiteAgent(['piva' => 'A2', 'name' => 'Da rimuovere']);
        $this->suiteBank(['piva' => 'B1']);
        $removedBank = $this->suiteBank(['piva' => 'B2', 'name' => 'Da rimuovere']);

        $manualProcessor = $this->localProcessor(['name' => 'Manuale P', 'vat_number' => 'ZZ']);
        $manualController = $this->localController(['name' => 'Manuale C']);

        $this->assertSame(2, $this->importAgents()->created);
        $this->assertSame(2, $this->importBanks()->created);

        $agents = $this->importAgents();
        $banks = $this->importBanks();
        $this->assertSame([0, 0, 2], [$agents->created, $agents->updated, $agents->unchanged]);
        $this->assertSame([0, 0, 2], [$banks->created, $banks->updated, $banks->unchanged]);

        DB::connection('unicosuite')->table('fornitoris')->where('id', $removedAgent)->update(['deleted_at' => now()]);
        DB::connection('unicosuite')->table('clientis')->where('id', $removedBank)->delete();

        $agents = $this->importAgents();
        $banks = $this->importBanks();
        $this->assertSame(1, $agents->deactivated);
        $this->assertSame(1, $banks->deactivated);
        $this->assertFalse(ExternalProcessor::query()->where('source_id', (string) $removedAgent)->firstOrFail()->is_active);
        $this->assertFalse(ClientController::query()->where('source_id', (string) $removedBank)->firstOrFail()->is_active);

        $again = $this->importAgents();
        $this->assertSame([0, 0, 0], [$again->updated, $again->deactivated, $again->created]);
        $this->assertSame(2, $again->unchanged);

        $this->assertTrue($manualProcessor->refresh()->is_active);
        $this->assertTrue($manualController->refresh()->is_active);
        $this->assertNull($manualProcessor->source_system);
    }

    public function test_termination_does_not_touch_rows_of_other_companies(): void
    {
        $this->suiteAgent();
        $this->importAgents();

        $other = Company::factory()->create();
        $foreign = ExternalProcessor::query()->create([
            'company_id' => $other->id,
            'name' => 'Estraneo',
            'is_active' => true,
            'source_system' => 'unicosuite:fornitoris',
            'source_id' => '9999',
        ]);

        $this->importAgents();

        $this->assertTrue($foreign->refresh()->is_active);
    }
}
