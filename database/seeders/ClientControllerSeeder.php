<?php

namespace Database\Seeders;

use App\Models\ClientController;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ClientControllerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo ClientControllerSeeder.');
            return;
        }

        $controllers = [
            [
                'name'                  => 'Mandante Energia S.p.A.',
                'vat_number'            => '01234567890',
                'address'               => 'Via Nazionale 100, 00100 Roma RM',
                'email'                 => 'privacy@mandanteenergia.it',
                'pec'                   => 'mandanteenergia@pec.it',
                'phone'                 => '+39 06 1234567',
                'dpo_contact'           => 'Avv. Giovanni Bianchi – gdpr@mandanteenergia.it',
                'agreement_description' => 'Accordo di contitolarità ex Art. 26 GDPR per le attività di vendita di contratti di fornitura energia. Le parti determinano congiuntamente finalità e mezzi del trattamento. {company_name} agisce come promotore/agente; Mandante Energia S.p.A. come titolare principale. La sintesi dell\'accordo è disponibile all\'interessato su richiesta.',
                'agreement_date'        => now()->subYear(),
                'is_active'             => true,
                'notes'                 => 'Accordo rinnovato annualmente. Ultima revisione: '.now()->subMonths(3)->format('d/m/Y'),
            ],
            [
                'name'                  => 'Finanza Partner S.r.l.',
                'vat_number'            => '09876543210',
                'address'               => 'Piazza Affari 5, 20100 Milano MI',
                'email'                 => 'dpo@finanzapartner.it',
                'pec'                   => 'finanzapartner@legalmail.it',
                'phone'                 => '+39 02 9876543',
                'dpo_contact'           => 'Dott.ssa Laura Neri',
                'agreement_description' => 'Contitolarità per la raccolta di richieste di finanziamento e la profilazione creditizia. Finanza Partner S.r.l. determina i criteri di scoring; la rete agenziale gestisce il contatto con il cliente e la raccolta del consenso informato.',
                'agreement_date'        => now()->subMonths(14),
                'is_active'             => true,
                'notes'                 => 'DPA e accordo Art. 26 archiviati in Documenti GDPR.',
            ],
            [
                'name'                  => 'Assicura Vita S.p.A.',
                'vat_number'            => '55566677788',
                'address'               => 'Corso Vittorio Emanuele 200, 10100 Torino TO',
                'email'                 => 'privacy@assicuravita.it',
                'pec'                   => 'assicuravita@pec.it',
                'phone'                 => '+39 011 9988776',
                'dpo_contact'           => 'privacy@assicuravita.it',
                'agreement_description' => 'Accordo di contitolarità per il trattamento di dati sanitari e anagrafici dei contraenti nelle polizze vita. Le finalità condivise includono: valutazione rischio, emissione polizze e gestione sinistri. Entrambe le parti nominano come DPO un soggetto terzo indipendente.',
                'agreement_date'        => now()->subMonths(8),
                'is_active'             => true,
                'notes'                 => 'Trattamento dati particolari (sanitari) – DPIA in corso.',
            ],
            [
                'name'                  => 'Ex Partner Gas S.r.l. (cessato)',
                'vat_number'            => '11122233344',
                'address'               => 'Via Industriale 7, 16100 Genova GE',
                'email'                 => 'info@expartnergas.it',
                'pec'                   => null,
                'phone'                 => null,
                'dpo_contact'           => null,
                'agreement_description' => 'Accordo di contitolarità cessato a seguito di risoluzione del rapporto commerciale. Dati acquisiti durante la collaborazione gestiti secondo retention policy definita nell\'accordo originario.',
                'agreement_date'        => now()->subYears(3),
                'is_active'             => false,
                'notes'                 => 'Accordo scaduto. Verifica eliminazione dati residui entro '.now()->addMonths(6)->format('d/m/Y').'.',
            ],
        ];

        foreach ($controllers as $data) {
            ClientController::firstOrCreate(
                ['vat_number' => $data['vat_number'], 'company_id' => $company->id],
                array_merge($data, ['company_id' => $company->id])
            );
        }

        $this->command->info(count($controllers).' client controllers seeded.');
    }
}
