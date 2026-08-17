<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ExternalProcessor;
use App\Models\PrivacySecurity;
use Illuminate\Database\Seeder;

class ExternalProcessorSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo ExternalProcessorSeeder.');
            return;
        }

        $processors = [
            [
                'name'                   => 'CloudHost Services Italia S.r.l.',
                'vat_number'             => '12345678901',
                'address'                => 'Via della Tecnica 10, 20100 Milano MI',
                'email'                  => 'privacy@cloudhost.it',
                'pec'                    => 'cloudhost@pec.it',
                'phone'                  => '+39 02 1234567',
                'dpo_contact'            => 'dpo@cloudhost.it',
                'processing_description' => 'Hosting infrastruttura cloud (server, database, backup) contenente dati personali di clienti e dipendenti. Trattamento per conto del titolare ex Art. 28 GDPR.',
                'contract_date'          => now()->subYear(),
                'is_active'              => true,
                'notes'                  => 'ISO 27001 certificato. Data center ubicati in UE (Frankfurt).',
                'security_ids'           => [1, 3, 6],  // Crittografia, Backup, VPN
            ],
            [
                'name'                   => 'Studio Consulenza del Lavoro Rossi & Partners',
                'vat_number'             => '98765432109',
                'address'                => 'Via Roma 55, 00100 Roma RM',
                'email'                  => 'gdpr@studiorossi.it',
                'pec'                    => 'studiorossi@legalmail.it',
                'phone'                  => '+39 06 9876543',
                'dpo_contact'            => 'Avv. Andrea Rossi',
                'processing_description' => 'Elaborazione paghe e contributi, gestione CUD e adempimenti previdenziali. Accesso a dati anagrafici, fiscali e bancari dei dipendenti.',
                'contract_date'          => now()->subMonths(6),
                'is_active'              => true,
                'notes'                  => 'DPA firmato e archiviato. Verificato annualmente.',
                'security_ids'           => [11, 16],   // Policy Privacy, Gestione Accessi
            ],
            [
                'name'                   => 'MailSender Pro S.r.l.',
                'vat_number'             => '55544433322',
                'address'                => 'Via Digitale 3, 40100 Bologna BO',
                'email'                  => 'privacy@mailsenderpro.it',
                'pec'                    => 'mailsenderpro@pec.it',
                'phone'                  => '+39 051 1122334',
                'dpo_contact'            => 'privacy@mailsenderpro.it',
                'processing_description' => 'Invio campagne email DEM, newsletter e comunicazioni transazionali. Tratta indirizzi email e dati comportamentali (aperture, click).',
                'contract_date'          => now()->subMonths(3),
                'is_active'              => true,
                'notes'                  => 'Conforme CASL e CAN-SPAM. Log invii conservati 12 mesi.',
                'security_ids'           => [10, 8],    // Secure Email Gateway, Patch Management
            ],
            [
                'name'                   => 'Gestionale CRM SaaS Ltd',
                'vat_number'             => 'GB123456789',
                'address'                => '10 Tech Street, London EC1A 1BB, UK',
                'email'                  => 'dpo@crmcloud.io',
                'pec'                    => null,
                'phone'                  => '+44 20 7123 4567',
                'dpo_contact'            => 'Sarah Compliance – dpo@crmcloud.io',
                'processing_description' => 'CRM cloud per la gestione di lead, clienti e storico commerciale. Server in UE (Dublin, Ireland – AWS).',
                'contract_date'          => now()->subYears(2),
                'is_active'              => true,
                'notes'                  => 'Clausole Contrattuali Standard (SCC) allegate al DPA per trasferimento UK → UE.',
                'security_ids'           => [1, 4, 6],  // Crittografia, 2FA, VPN
            ],
            [
                'name'                   => 'Agenzia Recupero Crediti Alpha S.p.A.',
                'vat_number'             => '11223344556',
                'address'                => 'Corso Europa 88, 10100 Torino TO',
                'email'                  => 'compliance@alpharc.it',
                'pec'                    => 'alpharc@pec.it',
                'phone'                  => '+39 011 5544332',
                'dpo_contact'            => 'Dott.ssa Verdi – privacy@alpharc.it',
                'processing_description' => 'Gestione pratiche di recupero crediti. Accede a dati anagrafici, recapiti, situazione debitoria e storico comunicazioni dei clienti morosi.',
                'contract_date'          => now()->subMonths(8),
                'is_active'              => false,
                'notes'                  => 'Contratto sospeso in attesa di rinnovo DPA. Verificare entro 30 giorni.',
                'security_ids'           => [],
            ],
        ];

        $allSecurities = PrivacySecurity::pluck('id')->toArray();

        foreach ($processors as $data) {
            $securityIds = $data['security_ids'];
            unset($data['security_ids']);

            $processor = ExternalProcessor::firstOrCreate(
                ['vat_number' => $data['vat_number'], 'company_id' => $company->id],
                array_merge($data, ['company_id' => $company->id])
            );

            // Collega misure di sicurezza se esistono
            $validIds = array_intersect($securityIds, $allSecurities);
            if (! empty($validIds)) {
                $processor->privacySecurities()->syncWithoutDetaching($validIds);
            }
        }

        $this->command->info(count($processors).' external processors seeded.');
    }
}
