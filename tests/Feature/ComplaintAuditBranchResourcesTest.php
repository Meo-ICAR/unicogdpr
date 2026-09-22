<?php

namespace Tests\Feature;

use App\Enums\AuditStatus;
use App\Enums\ComplaintStatus;
use App\Models\Audit;
use App\Models\Branch;
use App\Models\Clienti;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
