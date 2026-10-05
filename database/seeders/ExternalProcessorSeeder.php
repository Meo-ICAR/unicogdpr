<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExternalProcessor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExternalProcessorSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo ExternalProcessorSeeder.');
            return;
        }

        // Legge gli ID dal catalogo globale privacy_securities (non la tabella tenant)
        $catalogIds = DB::table('privacy_securities')->pluck('id', 'code');

        $processors = [
            [
                'name'                   => 'CloudHost Services Italia S.r.l.',
                'vat_number'             => '12345678901',
                'address'                => 'Via della Tecnica 10, 20100 Milano MI',
                'email'                  => 'privacy@cloudhost.it',
                'pec'                    => 'cloudhost@pec.it',
                'phone'                  => '+39 02 1234567',
                'dpo_contact'            => 'dpo@cloudhost.it',
                'processing_description' => 'Hosting infrastruttura cloud contenente dati personali di clienti e dipendenti.',
                'contract_date'          => now()->subYear(),
                'is_active'              => true,
                'notes'                  => 'ISO 27001 certificato. Data center in Frankfurt.',
                'security_codes'         => ['SEC-T01', 'SEC-T03', 'SEC-T06'],
            ],
            [
                'name'                   => 'Studio Consulenza del Lavoro Rossi & Partners',
                'vat_number'             => '98765432109',
                'address'                => 'Via Roma 55, 00100 Roma RM',
                'email'                  => 'gdpr@studiorossi.it',
                'pec'                    => 'studiorossi@legalmail.it',
                'phone'                  => '+39 06 9876543',
                'dpo_contact'            => 'Avv. Andrea Rossi',
                'processing_description' => 'Elaborazione paghe, CUD e adempimenti previdenziali.',
                'contract_date'          => now()->subMonths(6),
                'is_active'              => true,
                'notes'                  => 'DPA firmato e archiviato.',
                'security_codes'         => ['SEC-O01', 'SEC-T12'],
            ],
            [
                'name'                   => 'MailSender Pro S.r.l.',
                'vat_number'             => '55544433322',
                'address'                => 'Via Digitale 3, 40100 Bologna BO',
                'email'                  => 'privacy@mailsenderpro.it',
                'pec'                    => 'mailsenderpro@pec.it',
                'phone'                  => '+39 051 1122334',
                'dpo_contact'            => 'privacy@mailsenderpro.it',
                'processing_description' => 'Invio campagne email DEM e comunicazioni transazionali.',
                'contract_date'          => now()->subMonths(3),
                'is_active'              => true,
                'notes'                  => 'Certificazione DPF UE-USA attiva.',
                'security_codes'         => ['SEC-T10', 'SEC-T08'],
            ],
            [
                'name'                   => 'Gestionale CRM SaaS Ltd',
                'vat_number'             => 'GB123456789',
                'address'                => '10 Tech Street, London EC1A 1BB, UK',
                'email'                  => 'dpo@crmcloud.io',
                'pec'                    => null,
                'phone'                  => '+44 20 7123 4567',
                'dpo_contact'            => 'Sarah Compliance – dpo@crmcloud.io',
                'processing_description' => 'CRM cloud per gestione lead, clienti e storico commerciale.',
                'contract_date'          => now()->subYears(2),
                'is_active'              => true,
                'notes'                  => 'SCC allegate al DPA per trasferimento UK→UE.',
                'security_codes'         => ['SEC-T01', 'SEC-T04', 'SEC-T06'],
            ],
            [
                'name'                   => 'Agenzia Recupero Crediti Alpha S.p.A.',
                'vat_number'             => '11223344556',
                'address'                => 'Corso Europa 88, 10100 Torino TO',
                'email'                  => 'compliance@alpharc.it',
                'pec'                    => 'alpharc@pec.it',
                'phone'                  => '+39 011 5544332',
                'dpo_contact'            => 'Dott.ssa Verdi – privacy@alpharc.it',
                'processing_description' => 'Gestione pratiche di recupero crediti.',
                'contract_date'          => now()->subMonths(8),
                'is_active'              => false,
                'notes'                  => 'Contratto sospeso in attesa di rinnovo DPA.',
                'security_codes'         => [],
            ],
        ];

        foreach ($processors as $data) {
            $securityCodes = $data['security_codes'];
            unset($data['security_codes']);

            $processor = ExternalProcessor::firstOrCreate(
                ['vat_number' => $data['vat_number'], 'company_id' => $company->id],
                array_merge($data, ['company_id' => $company->id])
            );

            // Collega le misure dal catalogo globale
            $ids = collect($securityCodes)
                ->map(fn ($code) => $catalogIds[$code] ?? null)
                ->filter()
                ->values()
                ->toArray();

            if (! empty($ids)) {
                $processor->privacySecurities()->syncWithoutDetaching($ids);
            }
        }

        $this->command->info(count($processors).' external processors seeded.');
    }
}
