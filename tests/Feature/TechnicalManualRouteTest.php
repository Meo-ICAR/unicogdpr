<?php

namespace Tests\Feature;

use Tests\TestCase;

class TechnicalManualRouteTest extends TestCase
{
    public function test_technical_manual_is_served_as_html(): void
    {
        $this->get(route('filament.admin.pages.manuale-tecnico'))
            ->assertOk()
            ->assertHeader('content-type', 'text/html; charset=utf-8')
            ->assertSee('UnicoGDPR — Manuale Tecnico', false);
    }
}
