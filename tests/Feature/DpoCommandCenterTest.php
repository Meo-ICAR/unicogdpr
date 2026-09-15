<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DataBreach;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DpoCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_dpo_sees_alerts_only_for_companies_they_supervise(): void
    {
        $supervised = Company::factory()->create(['name' => 'Società Supervisionata']);
        $other = Company::factory()->create(['name' => 'Società Non Supervisionata']);

        $dpo = User::factory()->create();
        $dpo->companies()->attach($supervised, ['role' => 'dpo']);

        DataBreach::create([
            'company_id' => $supervised->id,
            'name' => 'Incidente supervisionato',
            'severity' => 'high',
            'status' => 'investigating',
            'discovered_at' => now()->subHours(80),
            'is_notifiable_to_authority' => true,
            'is_notifiable_to_subjects' => false,
        ]);

        DataBreach::create([
            'company_id' => $other->id,
            'name' => 'Incidente altra società',
            'severity' => 'high',
            'status' => 'investigating',
            'discovered_at' => now()->subHours(80),
            'is_notifiable_to_authority' => true,
            'is_notifiable_to_subjects' => false,
        ]);

        // Nota: il menu di cambio azienda di Filament elenca comunque tutte le
        // company a cui l'utente può accedere (canAccessTenant restituisce
        // sempre true in questa app): verifichiamo quindi che la RIGA della
        // tabella di supervisione mostri solo la company effettivamente
        // presidiata, cercando il badge di allerta accanto al suo nome.
        $response = $this->actingAs($dpo)
            ->get(route('filament.admin.pages.dpo-command-center', ['tenant' => $supervised->id]))
            ->assertOk();

        $response->assertSee('Società Supervisionata');
        $response->assertSee('1 scaduti');

        $content = $response->getContent();
        $rowStart = strpos($content, 'fi-ta-table');
        $tableSection = substr($content, $rowStart);

        $this->assertStringNotContainsString('Società Non Supervisionata', $tableSection);
    }
}
