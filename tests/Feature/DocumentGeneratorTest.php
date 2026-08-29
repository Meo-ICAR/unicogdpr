<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DataBreach;
use App\Models\DataProcessor;
use App\Models\Employee;
use App\Services\DocumentGeneratorService;
use Tests\TestCase;

class DocumentGeneratorTest extends TestCase
{
    public function test_can_generate_nomina_incaricato_pdf(): void
    {
        $service = app(DocumentGeneratorService::class);
        $employee = new Employee([
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'job_title' => 'Operatore Telemarketing',
            'department' => 'Contact Center',
            'tax_code' => 'RSSMRA80A01H501U',
        ]);

        $pdf = $service->generateNominaIncaricato($employee);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_can_generate_accordo_riservatezza_nda_pdf(): void
    {
        $service = app(DocumentGeneratorService::class);
        $employee = new Employee([
            'first_name' => 'Luigi',
            'last_name' => 'Verdi',
            'job_title' => 'Team Leader',
            'tax_code' => 'VRDLGU85B02H501X',
        ]);

        $pdf = $service->generateAccordoRiservatezza($employee);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_can_generate_dpa_subresponsabile_pdf(): void
    {
        $service = app(DocumentGeneratorService::class);
        $processor = new DataProcessor([
            'name' => 'Cloud Provider Solutions S.r.l.',
            'tax_number' => 'IT01234567890',
            'contact_email' => 'privacy@cloudprovider.it',
            'has_dpa_signed' => true,
            'dpa_signed_at' => now(),
            'dpa_expires_at' => now()->addYear(),
        ]);

        $pdf = $service->generateDpaSubresponsabile($processor);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }

    public function test_can_generate_data_breach_dossier_pdf(): void
    {
        $service = app(DocumentGeneratorService::class);
        $breach = new DataBreach([
            'name' => 'DB-2026-001 Tentativo accesso anomalo database',
            'severity' => 'medium',
            'status' => 'investigating',
            'discovered_at' => now(),
            'is_notifiable_to_authority' => true,
            'is_notifiable_to_subjects' => false,
            'description' => 'Rilevato picco anomalo di tentativi di accesso su credenziali operatore dismesso.',
            'approximate_records_count' => 150,
        ]);

        $pdf = $service->generateNotificaDataBreach($breach);
        $output = $pdf->output();

        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF-', $output);
    }
}
