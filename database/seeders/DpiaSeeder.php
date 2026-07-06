<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Dpia;
use App\Models\RegistroTrattamentiItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DpiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate tables in correct order to handle foreign key constraints
        DB::table('dpia_items')->delete();
        DB::table('dpias')->delete();

        // Get companies and registro trattamenti items for relationships
        $companies = Company::all();
        $registroItems = RegistroTrattamentiItem::all();

        if ($companies->isEmpty() || $registroItems->isEmpty()) {
            $this->command->warn('No companies or registro trattamenti items found. Skipping DPIA seeding.');
            return;
        }

        // Sample DPIA records
        $dpias = [
            [
                'name' => 'DPIA - Sistema di Monitoraggio Clienti',
                'description_of_processing' => 'Monitoraggio sistematico e continuo del comportamento dei clienti attraverso analisi predittive e profilazione avanzata per valutare il rischio di frode e la solvibilità creditizia.',
                'necessity_assessment' => 'Il trattamento è necessario per garantire la sicurezza delle transazioni finanziarie e prevenire frodi. Il monitoraggio sistematico è richiesto dalla normativa antiriciclaggio e dalle policy interne di gestione del rischio.',
                'is_necessary' => true,
                'is_proportional' => true,
                'status' => 'completed',
                'dpo_opinion' => "Il trattamento è proporzionato e necessario. Si raccomanda l'implementazione di misure di sicurezza aggiuntive per la protezione dei dati biometrici e comportamentali.",
                'completion_date' => now()->subMonths(6),
                'next_review_date' => now()->addMonths(6),
            ],
            [
                'name' => 'DPIA - Piattaforma di Marketing Automation',
                'description_of_processing' => 'Utilizzo di algoritmi di intelligenza artificiale per la profilazione avanzata degli utenti e la personalizzazione automatica delle campagne di marketing su canali digitali.',
                'necessity_assessment' => "Il trattamento è necessario per ottimizzare le strategie di marketing e migliorare l'esperienza utente. La profilazione permette di offrire contenuti rilevanti e ridurre il marketing non desiderato.",
                'is_necessary' => true,
                'is_proportional' => false,
                'status' => 'under_review',
                'dpo_opinion' => 'Il trattamento presenta rischi elevati per i diritti e libertà degli interessati. Si raccomanda di limitare la raccolta dati e implementare un sistema di opt-out granulare.',
                'completion_date' => null,
                'next_review_date' => now()->addMonths(2),
            ],
            [
                'name' => 'DPIA - Sistema di Videosorveglianza Intelligente',
                'description_of_processing' => 'Installazione di telecamere con riconoscimento facciale e analisi comportamentale in aree pubbliche e private per la sicurezza fisica degli ambienti di lavoro.',
                'necessity_assessment' => 'Il trattamento è necessario per garantire la sicurezza dei dipendenti e dei beni aziendali. Il riconoscimento facciale permette un accesso controllato e il monitoraggio di aree sensibili.',
                'is_necessary' => true,
                'is_proportional' => true,
                'status' => 'draft',
                'dpo_opinion' => null,
                'completion_date' => null,
                'next_review_date' => null,
            ],
            [
                'name' => 'DPIA - Sistema di Whistleblowing',
                'description_of_processing' => "Piattaforma anonima per la segnalazione di illeciti e irregolarità, con tracciamento IP e analisi linguistica per verificare l'autenticità delle segnalazioni.",
                'necessity_assessment' => "Il trattamento è necessario per adempiere agli obblighi di legge sulla trasparenza e prevenzione della corruzione. L'anonimato è garantito ma richiede verifiche tecniche.",
                'is_necessary' => true,
                'is_proportional' => true,
                'status' => 'completed',
                'dpo_opinion' => 'Il sistema è conforme al GDPR. Si raccomanda di informare chiaramente gli utenti sulle modalità di trattamento e conservazione delle segnalazioni.',
                'completion_date' => now()->subMonths(3),
                'next_review_date' => now()->addMonths(9),
            ],
            [
                'name' => 'DPIA - Analisi Genetica per Assicurazioni',
                'description_of_processing' => 'Trattamento di dati genetici per la valutazione del rischio assicurativo e la personalizzazione delle polizze vita e sanitarie.',
                'necessity_assessment' => "Il trattamento presenta rischi elevati e richiede un'analisi dettagliata. È necessario solo per polizze specifiche con consenso esplicito.",
                'is_necessary' => false,
                'is_proportional' => false,
                'status' => 'under_review',
                'dpo_opinion' => "Il trattamento non è proporzionato allo scopo. Si raccomanda di utilizzare metodi alternativi di valutazione del rischio e limitare l'uso di dati genetici.",
                'completion_date' => null,
                'next_review_date' => now()->addMonths(1),
            ],
            [
                'name' => 'DPIA - Sistema di IoT Industriale',
                'description_of_processing' => 'Raccolta e analisi di dati da sensori IoT per il monitoraggio della produzione, manutenzione predittiva e ottimizzazione dei processi industriali.',
                'necessity_assessment' => "Il trattamento è necessario per l'automazione industriale e l'efficienza produttiva. I dati raccolti permettono di prevenire guasti e ottimizzare i consumi.",
                'is_necessary' => true,
                'is_proportional' => true,
                'status' => 'completed',
                'dpo_opinion' => "Il trattamento è conforme. Si raccomanda di implementare crittografia end-to-end per i dati trasmessi dai sensori e limitare l'accesso ai dati aggregati.",
                'completion_date' => now()->subMonths(12),
                'next_review_date' => now()->addMonths(6),
            ],
            [
                'name' => 'DPIA - Piattaforma di e-Learning con Biometria',
                'description_of_processing' => "Sistema di formazione online con monitoraggio biometrico (riconoscimento facciale, tracciamento oculare) per verificare l'identità degli studenti e il livello di attenzione.",
                'necessity_assessment' => 'Il trattamento è necessario per garantire la validità delle certificazioni e prevenire frodi negli esami online. I dati biometrici sono minimizzati e protetti.',
                'is_necessary' => true,
                'is_proportional' => false,
                'status' => 'draft',
                'dpo_opinion' => null,
                'completion_date' => null,
                'next_review_date' => null,
            ],
            [
                'name' => 'DPIA - Sistema di Credit Scoring con AI',
                'description_of_processing' => "Utilizzo di machine learning per l'analisi di dati finanziari, comportamentali e social per la determinazione del merito creditizio.",
                'necessity_assessment' => 'Il trattamento è necessario per la valutazione del rischio di credito ma richiede trasparenza e possibilità di ricorso umano.',
                'is_necessary' => true,
                'is_proportional' => true,
                'status' => 'completed',
                'dpo_opinion' => 'Il sistema è conforme se implementato con spiegabilità degli algoritmi e processo di revisione umana obbligatoria.',
                'completion_date' => now()->subMonths(8),
                'next_review_date' => now()->addMonths(4),
            ],
        ];

        // Create DPIA records for each company
        foreach ($companies as $company) {
            // Assign random registro trattamenti items to DPIAs
            $assignedItems = $registroItems->random(min(5, $registroItems->count()));

            foreach ($dpias as $index => $dpiaData) {
                // Create DPIA with company_id and random registro item
                $dpia = Dpia::create(array_merge($dpiaData, [
                    'company_id' => $company->id,
                    'registro_trattamenti_item_id' => $assignedItems->get($index % $assignedItems->count())->id,
                ]));

                $this->command->info("Created DPIA: {$dpia->name} for company: {$company->name}");
            }
        }

        $this->command->info('DPIA seeding completed successfully!');
    }
}
