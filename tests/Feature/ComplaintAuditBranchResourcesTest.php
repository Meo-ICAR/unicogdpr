<?php

namespace Tests\Feature;

use App\Enums\AuditChecklistGapStatus;
use App\Enums\AuditStatus;
use App\Enums\ComplaintStatus;
use App\Filament\Resources\ComplaintRegistries\Pages\CreateComplaintRegistry;
use App\Filament\Resources\DataSubjectRequests\Pages\EditDataSubjectRequest;
use App\Filament\Resources\DataSubjectRequests\RelationManagers\ComplaintEventsRelationManager;
use App\Filament\Widgets\AuditsOverviewWidget;
use App\Filament\Widgets\DsarOverviewWidget;
use App\Models\Audit;
use App\Models\AuditChecklistEvaluation;
use App\Models\AuditChecklistItem;
use App\Models\Branch;
use App\Models\Clienti;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\DataSubjectRequest;
use App\Models\ProcessingActivity;
use App\Models\User;
use App\Services\DocumentGeneratorService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * ComplaintRegistry, Audit e Branch vivono sulla connessione condivisa
 * 'mysql_unicooam' (non coperta da RefreshDatabase, che gestisce solo la
 * connessione di default): i record di test creati qui vengono avvolti in
 * una transazione esplicita su quella connessione e sempre annullati in
 * tearDown, per non lasciare dati di test nel database reale condiviso con
 * le altre app della suite.
 */
class ComplaintAuditBranchResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::connection('mysql_unicooam')->beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::connection('mysql_unicooam')->rollBack();

        parent::tearDown();
    }

    /**
     * complaint_registry/audits/branches vivono sulla connessione condivisa
     * e referenziano, per company_id/branchable_id, la tabella
     * "unicooam.companies" (distinta dalla tabella companies dell'app,
     * sulla connessione di default) — qui ne creiamo una temporanea,
     * anch'essa dentro la transazione annullata in tearDown.
     */
    private function makeUnicooamCompanyId(): string
    {
        $id = (string) Str::uuid();

        DB::connection('mysql_unicooam')->table('companies')->insert([
            'id' => $id,
            'name' => 'Azienda Test Unicooam',
        ]);

        return $id;
    }

    public function test_dpo_can_open_the_complaint_registry_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $complaint = ComplaintRegistry::create([
            'company_id' => $company->id,
            'protocol_number' => 'REC-TEST-001',
            'received_at' => now(),
            'status' => ComplaintStatus::Received->value,
            'description' => 'Reclamo di test.',
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.complaint-registries.edit', ['tenant' => $company->id, 'record' => $complaint->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_complaint_registry_list_page_with_the_export_action(): void
    {
        $company = Company::factory()->create();
        ComplaintRegistry::create([
            'company_id' => $company->id,
            'protocol_number' => 'REC-TEST-002',
            'event_sequence' => 1,
            'received_at' => now(),
            'status' => ComplaintStatus::Received->value,
            'description' => 'Reclamo di test.',
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.complaint-registries.index', ['tenant' => $company->id]))
            ->assertOk()
            ->assertSee('Esporta Excel');
    }

    public function test_can_generate_scheda_reclamo_pdf_aggregating_all_events_of_a_protocol(): void
    {
        $company = Company::factory()->create(['name' => 'Azienda Test Scheda']);

        foreach ([1, 2] as $sequence) {
            ComplaintRegistry::create([
                'company_id' => $company->id,
                'protocol_number' => 'REC-TEST-004',
                'event_sequence' => $sequence,
                'event_at' => now(),
                'event_phase' => "Evento di test {$sequence}",
                'mandating_company' => 'Titolare Test S.p.A.',
                'master_agency' => 'Responsabile Test S.r.l.',
                'sub_supplier' => 'Vendor Test',
                'received_at' => now(),
                'complainant_name' => 'Mario Rossi',
                'complainant_fiscal_code' => 'RSSMRA80A01H501U',
                'complainant_email' => 'mario.rossi@pec.it',
                'complainant_phone' => '3331234567',
                'description' => "Descrizione evento di test {$sequence}.",
                'operational_action' => "Azione di test {$sequence}.",
                'dnc_blacklist_status' => 'Nessuna',
                'log_freeze_retention' => 'N/A',
                'phase_status' => $sequence === 2 ? 'Chiuso' : 'In Lavorazione',
                'assigned_to' => 'DPO Test',
                'event_channel_label' => 'PEC',
                'event_direction' => $sequence === 1 ? 'Inbound' : 'Outbound',
                'event_counterparty' => 'Rossi -> Test',
                'status' => ComplaintStatus::Received->value,
            ]);
        }

        $service = app(DocumentGeneratorService::class);
        $pdf = $service->generateSchedaReclamo('REC-TEST-004');
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_generate_scheda_reclamo_throws_for_an_unknown_protocol(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(DocumentGeneratorService::class)->generateSchedaReclamo('PROTOCOLLO-INESISTENTE');
    }

    public function test_dpo_can_open_the_audit_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $unicooamCompanyId = $this->makeUnicooamCompanyId();

        $audit = Audit::create([
            'company_id' => $unicooamCompanyId,
            'auditable_type' => 'company',
            'auditable_id' => $company->id,
            'auditor_name' => 'Test Auditor',
            'status' => AuditStatus::Scheduled->value,
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.audits.edit', ['tenant' => $company->id, 'record' => $audit->id]))
            ->assertOk();
    }

    public function test_dpo_can_open_the_audit_edit_page_with_the_checklist_relation_manager(): void
    {
        $company = Company::factory()->create();
        $unicooamCompanyId = $this->makeUnicooamCompanyId();

        $audit = Audit::create([
            'company_id' => $unicooamCompanyId,
            'auditable_type' => 'company',
            'auditable_id' => $company->id,
            'auditor_name' => 'Test Auditor',
            'status' => AuditStatus::Scheduled->value,
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.audits.edit', ['tenant' => $company->id, 'record' => $audit->id]))
            ->assertOk()
            ->assertSee('Checklist Documentale');
    }

    public function test_dpo_can_open_the_audit_checklist_item_catalog_list_page(): void
    {
        $company = Company::factory()->create();
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.audit-checklist-items.index', ['tenant' => $company->id]))
            ->assertOk();
    }

    /**
     * Copre proprio il bug incontrato costruendo la feature: BelongsToMany
     * di Eloquent risolve la connessione della tabella pivot da quella del
     * modello "related" (ProcessingActivity, connessione di default), non
     * da quella del modello di partenza (AuditChecklistEvaluation, su
     * mysql_unicooam) — la pivot deve quindi vivere sulla connessione di
     * default, non su mysql_unicooam, altrimenti la query cade sul database
     * sbagliato.
     */
    public function test_checklist_evaluation_relations_work_across_connections(): void
    {
        $company = Company::factory()->create();
        $unicooamCompanyId = $this->makeUnicooamCompanyId();

        $audit = Audit::create([
            'company_id' => $unicooamCompanyId,
            'auditable_type' => 'company',
            'auditable_id' => $company->id,
            'auditor_name' => 'Test Auditor',
            'status' => AuditStatus::Scheduled->value,
        ]);

        $item = AuditChecklistItem::create([
            'category' => 'documentazione_compliance',
            'title' => 'Voce di test',
            'is_mandatory' => true,
            'sort_order' => 0,
        ]);

        $processingActivity = ProcessingActivity::create([
            'company_id' => $company->id,
            'name' => 'Trattamento di test',
            'role' => 'controller',
        ]);

        $evaluation = AuditChecklistEvaluation::create([
            'audit_id' => $audit->id,
            'audit_checklist_item_id' => $item->id,
            'gap_status' => AuditChecklistGapStatus::Parziale->value,
        ]);

        $this->assertSame($item->id, $evaluation->checklistItem->id);

        $evaluation->processingActivities()->attach($processingActivity->id, ['paragraph' => 'par. 3.1']);
        $evaluation->refresh();

        $this->assertCount(1, $evaluation->processingActivities);
        $this->assertSame('par. 3.1', $evaluation->processingActivities->first()->pivot->paragraph);
        $this->assertTrue($audit->checklistEvaluations()->whereKey($evaluation->id)->exists());
    }

    public function test_dpo_can_open_the_checklist_evaluation_edit_page_with_its_relation_managers(): void
    {
        $company = Company::factory()->create();
        $unicooamCompanyId = $this->makeUnicooamCompanyId();

        $audit = Audit::create([
            'company_id' => $unicooamCompanyId,
            'auditable_type' => 'company',
            'auditable_id' => $company->id,
            'auditor_name' => 'Test Auditor',
            'status' => AuditStatus::Scheduled->value,
        ]);

        $item = AuditChecklistItem::create([
            'category' => 'documentazione_compliance',
            'title' => 'Voce di test',
            'is_mandatory' => true,
            'sort_order' => 0,
        ]);

        $evaluation = AuditChecklistEvaluation::create([
            'audit_id' => $audit->id,
            'audit_checklist_item_id' => $item->id,
            'gap_status' => AuditChecklistGapStatus::DaVerificare->value,
        ]);

        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.audit-checklist-evaluations.edit', ['tenant' => $company->id, 'record' => $evaluation->id]))
            ->assertOk()
            ->assertSee('Trattamenti Aziendali Coinvolti');
    }

    public function test_dpo_can_see_multicompany_audit_and_dsar_widgets_on_the_dashboard(): void
    {
        $company = Company::factory()->create(['name' => 'Azienda Widget Test']);
        $unicooamCompanyId = $this->makeUnicooamCompanyId();

        Audit::create([
            'company_id' => $unicooamCompanyId,
            'auditable_type' => 'company',
            'auditable_id' => $company->id,
            'protocol_number' => 'AUDIT-WIDGET-TEST-001',
            'auditor_name' => 'Auditor Widget Test',
            'status' => AuditStatus::InProgress->value,
        ]);

        DataSubjectRequest::create([
            'company_id' => $company->id,
            'requester_name' => 'Richiedente Widget Test',
            'requester_email' => 'widget.test@example.com',
            'request_type' => 'access',
            'status' => 'received',
            'received_at' => now(),
            'deadline_at' => now()->addDays(30),
        ]);

        $dpo = User::factory()->create();
        $this->actingAs($dpo);

        // Le tabelle dei widget si caricano via un componente Livewire a sé
        // stante (lazy-load nella dashboard): li testiamo direttamente
        // invece di cercarne il contenuto nell'HTML iniziale della pagina.
        Livewire::test(AuditsOverviewWidget::class)
            ->assertSee('AUDIT-WIDGET-TEST-001');

        Livewire::test(DsarOverviewWidget::class)
            ->assertSee('Richiedente Widget Test');
    }

    public function test_dsar_overview_widget_filters_by_deadline_range(): void
    {
        $company = Company::factory()->create();

        DataSubjectRequest::create([
            'company_id' => $company->id,
            'requester_name' => 'Richiedente Scadenza Agosto',
            'requester_email' => 'agosto.test@example.com',
            'request_type' => 'access',
            'status' => 'received',
            'received_at' => '2026-07-15',
            'deadline_at' => '2026-08-15',
        ]);

        DataSubjectRequest::create([
            'company_id' => $company->id,
            'requester_name' => 'Richiedente Scadenza Ottobre',
            'requester_email' => 'ottobre.test@example.com',
            'request_type' => 'access',
            'status' => 'received',
            'received_at' => '2026-09-15',
            'deadline_at' => '2026-10-15',
        ]);

        $dpo = User::factory()->create();
        $this->actingAs($dpo);

        Livewire::test(DsarOverviewWidget::class)
            ->filterTable('deadline_at', ['deadline_from' => '2026-08-01', 'deadline_until' => '2026-08-31'])
            ->assertSee('Richiedente Scadenza Agosto')
            ->assertDontSee('Richiedente Scadenza Ottobre');
    }

    /**
     * DataSubjectRequest è il "master" del fascicolo: complaint_registry
     * (mysql_unicooam) referenzia data_subject_request_id (riferimento
     * debole, connessione di default) — non è più un abbinamento per sola
     * stringa protocol_number.
     */
    public function test_dsar_edit_page_shows_complaint_events_sharing_the_same_protocol_number(): void
    {
        $company = Company::factory()->create();

        $dsar = DataSubjectRequest::create([
            'company_id' => $company->id,
            'protocol_number' => 'REC-TEST-005',
            'requester_name' => 'Richiedente Collegato Test',
            'requester_email' => 'collegato.test@example.com',
            'request_type' => 'access',
            'status' => 'received',
            'received_at' => now(),
            'deadline_at' => now()->addDays(30),
        ]);

        foreach ([1, 2] as $sequence) {
            ComplaintRegistry::create([
                'company_id' => $company->id,
                'protocol_number' => 'REC-TEST-005',
                'data_subject_request_id' => $dsar->id,
                'event_sequence' => $sequence,
                'event_phase' => "Evento di test {$sequence}",
                'received_at' => now(),
                'description' => "Descrizione evento {$sequence}.",
                'phase_status' => 'In Lavorazione',
                'status' => ComplaintStatus::Received->value,
            ]);
        }

        $this->assertCount(2, $dsar->complaintRegistryEntries);

        $dpo = User::factory()->create();
        $this->actingAs($dpo);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($company);

        Livewire::test(ComplaintEventsRelationManager::class, [
            'ownerRecord' => $dsar,
            'pageClass' => EditDataSubjectRequest::class,
        ])
            ->assertSee('Evento di test 1')
            ->assertSee('Evento di test 2');
    }

    /**
     * Il form Reclami precompila N° Progressivo e i dati anagrafici/filiera
     * dall'ultimo evento quando si digita un protocollo già esistente,
     * cosi da poter aggiungere un nuovo evento a un fascicolo senza doverli
     * riscrivere da capo.
     */
    public function test_complaint_registry_create_form_prefills_next_event_from_existing_protocol(): void
    {
        $company = Company::factory()->create();

        ComplaintRegistry::create([
            'company_id' => $company->id,
            'protocol_number' => 'REC-TEST-006',
            'event_sequence' => 1,
            'mandating_company' => 'Titolare Test S.p.A.',
            'complainant_name' => 'Mario Rossi',
            'received_at' => now(),
            'description' => 'Primo evento.',
            'status' => ComplaintStatus::Received->value,
        ]);

        $dpo = User::factory()->create();
        $this->actingAs($dpo);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($company);

        Livewire::test(CreateComplaintRegistry::class)
            ->set('data.protocol_number', 'REC-TEST-006')
            ->assertSet('data.event_sequence', 2)
            ->assertSet('data.mandating_company', 'Titolare Test S.p.A.')
            ->assertSet('data.complainant_name', 'Mario Rossi');
    }

    public function test_dpo_can_open_the_branch_edit_page_with_the_documents_relation_manager(): void
    {
        $company = Company::factory()->create();
        $unicooamCompanyId = $this->makeUnicooamCompanyId();

        $branch = Branch::create([
            'company_id' => $unicooamCompanyId,
            'name' => 'Sede di Test',
            'branchable_type' => 'company',
            'branchable_id' => $unicooamCompanyId,
        ]);
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.branches.edit', ['tenant' => $company->id, 'record' => $branch->id]))
            ->assertOk();
    }

    /**
     * Clienti vive sulla connessione condivisa 'proforma': a differenza
     * degli altri test qui, non creiamo righe (evitiamo di dover gestire
     * anche quella connessione in transazione) e usiamo invece un record
     * reale già presente, in sola lettura.
     */
    public function test_dpo_can_open_the_clienti_edit_page_with_the_documents_and_audits_relation_managers(): void
    {
        $cliente = Clienti::first();

        if (! $cliente) {
            $this->markTestSkipped('Nessun Clienti disponibile su cui verificare la pagina.');
        }

        $company = Company::factory()->create();
        $dpo = User::factory()->create();

        $this->actingAs($dpo)
            ->get(route('filament.admin.resources.clientis.edit', ['tenant' => $company->id, 'record' => $cliente->id]))
            ->assertOk();
    }
}
