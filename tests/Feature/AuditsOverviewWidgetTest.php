<?php

namespace Tests\Feature;

use App\Enums\AuditStatus;
use App\Filament\Resources\ClientControllers\ClientControllerResource;
use App\Filament\Widgets\AuditsOverviewWidget;
use App\Models\Audit;
use App\Models\ClientController;
use App\Models\Company;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AuditsOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_the_name_of_the_audited_subject_even_when_it_belongs_to_another_tenant(): void
    {
        $auditCompany = Company::factory()->create();
        $currentTenant = Company::factory()->create();

        $controllerId = DB::table('client_controllers')->insertGetId([
            'company_id' => $auditCompany->id,
            'name' => 'ECOM',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = Audit::withoutEvents(fn () => Audit::create([
            'company_id' => $auditCompany->id,
            'auditable_type' => 'client_controller',
            'auditable_id' => $controllerId,
            'protocol_number' => 'AUDIT-ECOM',
            'status' => AuditStatus::InProgress->value,
            'scheduled_at' => '2026-09-10',
        ]));

        $user = User::factory()->create();
        $user->companies()->attach($currentTenant, ['role' => 'dpo']);
        $this->actingAs($user);

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        ClientControllerResource::registerTenancyModelGlobalScope($panel);
        Filament::setTenant($currentTenant);

        $this->assertNull(ClientController::find($controllerId));

        Livewire::test(AuditsOverviewWidget::class)
            ->assertCanSeeTableRecords([$audit])
            ->assertSee('ECOM')
            ->assertDontSee('Client Controller');
    }
}
