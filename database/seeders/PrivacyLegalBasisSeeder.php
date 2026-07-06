<?php

namespace Database\Seeders;

use App\Models\PrivacyLegalBasis;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrivacyLegalBasisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legalBases = [
            [
                'name' => 'Consenso',
                'reference_article' => 'Art. 6 par. 1 lett. a)',
                'description' => "L'interessato ha espresso il consenso al trattamento dei propri dati personali per una o più finalità specifiche. Il consenso deve essere libero, specifico, informato e inequivocabile.",
            ],
            [
                'name' => 'Contratto',
                'reference_article' => 'Art. 6 par. 1 lett. b)',
                'description' => "Il trattamento è necessario all'esecuzione di un contratto di cui l'interessato è parte o all'esecuzione di misure precontrattuali adottate su richiesta dello stesso.",
            ],
            [
                'name' => 'Obbligo Legale',
                'reference_article' => 'Art. 6 par. 1 lett. c)',
                'description' => "Il trattamento è necessario per adempiere un obbligo legale al quale è soggetto il titolare del trattamento, come previsto dal diritto dell'Unione o dello Stato membro.",
            ],
            [
                'name' => 'Interesse Vitale',
                'reference_article' => 'Art. 6 par. 1 lett. d)',
                'description' => "Il trattamento è necessario per salvaguardare gli interessi vitali dell'interessato o di un'altra persona fisica, in particolare in caso di emergenza medica o di sicurezza.",
            ],
            [
                'name' => 'Interesse Pubblico',
                'reference_article' => 'Art. 6 par. 1 lett. e)',
                'description' => "Il trattamento è necessario per l'esecuzione di un compito svolto nell'interesse pubblico o connesse all'esercizio di pubblici poteri a cui è investito il titolare.",
            ],
            [
                'name' => 'Interesse Legittimo',
                'reference_article' => 'Art. 6 par. 1 lett. f)',
                'description' => "Il trattamento è necessario per il perseguimento del legittimo interesse del titolare del trattamento o di terzi, a condizione che non prevalgano gli interessi o i diritti fondamentali dell'interessato.",
            ],
            [
                'name' => 'Dati Particolari - Consenso Esplicito',
                'reference_article' => 'Art. 9 par. 2 lett. a)',
                'description' => "Trattamento di dati particolari (razza, origine etnica, opinioni politiche, religione, dati genetici, biometrici, salute) con consenso esplicito dell'interessato.",
            ],
            [
                'name' => 'Dati Particolari - Obbligo di Diritto del Lavoro',
                'reference_article' => 'Art. 9 par. 2 lett. b)',
                'description' => "Trattamento necessario per adempiere obblighi ed esercitare diritti specifici del titolare o dell'interessato in materia di diritto del lavoro e della previdenza sociale.",
            ],
            [
                'name' => 'Dati Particolari - Interessi Vitali',
                'reference_article' => 'Art. 9 par. 2 lett. c)',
                'description' => "Trattamento necessario per proteggere gli interessi vitali dell'interessato o di un'altra persona fisica, nel caso in cui l'interessato si trovi fisicamente o legalmente incapace di dare il proprio consenso.",
            ],
            [
                'name' => 'Dati Particolari - Fondazioni/Associazioni',
                'reference_article' => 'Art. 9 par. 2 lett. d)',
                'description' => "Trattamento di dati effettuato nell'ambito delle legittime attività svolta da fondazioni, associazioni o altri organismi senza scopo di lucro, con adeguate garanzie.",
            ],
            [
                'name' => 'Dati Particolari - Dati Pubblici',
                'reference_article' => 'Art. 9 par. 2 lett. e)',
                'description' => "Trattamento di dati resi pubblici dall'interessato stesso, nel rispetto delle limitazioni e condizioni previste dalla normativa.",
            ],
            [
                'name' => 'Dati Particolari - Accertamento Giudiziario',
                'reference_article' => 'Art. 9 par. 2 lett. f)',
                'description' => "Trattamento necessario per la dichiarazione, l'esercizio o la difesa di un diritto in sede giudiziaria o nei procedimenti amministrativi o stragiudiziali.",
            ],
            [
                'name' => 'Dati Particolari - Interesse Pubblico',
                'reference_article' => 'Art. 9 par. 2 lett. g)',
                'description' => "Trattamento necessario per motivi di interesse pubblico rilevante, basato sul diritto dell'Unione o degli Stati membri, proporzionato rispetto allo scopo perseguito.",
            ],
            [
                'name' => 'Dati Particolari - Sanità',
                'reference_article' => 'Art. 9 par. 2 lett. h)',
                'description' => "Trattamento necessario per finalità di medicina preventiva, diagnosi, assistenza o gestione di servizi sanitari e sociali, basato sul diritto dell'Unione o degli Stati membri.",
            ],
            [
                'name' => 'Dati Particolari - Archivio Interesse Pubblico',
                'reference_article' => 'Art. 9 par. 2 lett. j)',
                'description' => 'Trattamento necessario a fini di archiviazione nel pubblico interesse, per la ricerca scientifica o storica o per fini statistici, con adeguate garanzie.',
            ],
        ];

        foreach ($legalBases as $basis) {
            PrivacyLegalBasis::create($basis);
        }

        $this->command->info('Privacy legal bases seeded successfully!');
    }
}
