<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentationNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_documentation_links_appear_in_the_admin_navigation(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'admin']);

        $this->actingAs($user)
            ->get("/admin/{$company->id}")
            ->assertOk()
            ->assertSee('Documentazione')
            ->assertSee('Manuale Tecnico')
            ->assertSee('Manuale Operativo (PDF)')
            ->assertSee(route('filament.admin.pages.manuale-tecnico'), false);
    }
}
