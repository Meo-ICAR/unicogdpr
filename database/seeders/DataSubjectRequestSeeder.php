<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Company;
use App\Models\DataSubjectRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataSubjectRequestSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('data_subject_requests')->truncate();
        Schema::enableForeignKeyConstraints();

        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata per DataSubjectRequestSeeder.');
            return;
        }

        $client = Client::where('company_id', $company->id)->first();

        $requests = [
            // ── 1. Diritto alla Cancellazione (Art. 17 GDPR) - Completata ────────
            [
                'company_id'                  => $company->id,
                'registrable_type'            => $client ? Client::class : null,
                'registrable_id'              => $client?->id,
                'requester_name'              => 'Marco Rossi',
                'requester_email'             => 'marco.rossi78@example.com',
                'requester_phone'             => '+39 333 4567890',
                'request_type'                => 'erasure',
                'status'                      => 'completed',
                'received_at'                 => now()->subDays(25),
                'deadline_at'                 => now()->subDays(25)->addDays(30),
                'completed_at'                => now()->subDays(5),
                'request_description'         => 'Richiesta di cancellazione integrale dei dati personali presenti nei database marketing e contatti promozionali.',
                'response_notes'              => 'Dati cancellati dai CRM e dalle liste di contatto marketing in data '.now()->subDays(5)->format('d/m/Y').'. Inviata comunicazione di conferma all\'interessato.',
                'identity_verified'           => true,
                'identity_verification_method'=> 'Copia Carta d\'Identità allegata e verificata',
                'channel'                     => 'PEC',
            ],

            // ── 2. Diritto di Accesso (Art. 15 GDPR) - In Lavorazione ───────────
            [
                'company_id'                  => $company->id,
                'registrable_type'            => $client ? Client::class : null,
                'registrable_id'              => $client?->id,
                'requester_name'              => 'Elena Ferri',
                'requester_email'             => 'elena.ferri@example.org',
                'requester_phone'             => '+39 347 1122334',
                'request_type'                => 'access',
                'status'                      => 'in_progress',
                'received_at'                 => now()->subDays(12),
                'deadline_at'                 => now()->subDays(12)->addDays(30),
                'completed_at'                => null,
                'request_description'         => 'Richiesta di conoscere le categorie di dati personali trattati, le finalità, i destinatari e il periodo di conservazione previsto.',
                'response_notes'              => 'Richiesta presa in carico dal DPO. In fase di estrazione report dai sistemi gestionali.',
                'identity_verified'           => true,
                'identity_verification_method'=> 'Verifica tramite credenziali area riservata',
                'channel'                     => 'Email',
            ],

            // ── 3. Diritto alla Portabilità dei Dati (Art. 20 GDPR) - Prorogata ─
            [
                'company_id'                  => $company->id,
                'registrable_type'            => null,
                'registrable_id'              => null,
                'requester_name'              => 'Giuseppe Verdi',
                'requester_email'             => 'g.verdi@pec-example.it',
                'requester_phone'             => '+39 320 9988776',
                'request_type'                => 'portability',
                'status'                      => 'in_progress',
                'received_at'                 => now()->subDays(28),
                'deadline_at'                 => now()->subDays(28)->addDays(30),
                'extended_until'              => now()->subDays(28)->addDays(90),
                'completed_at'                => null,
                'request_description'         => 'Richiesta di ricezione in formato strutturato, di uso comune e leggibile da dispositivo automatico (JSON/CSV) dei dati generati.',
                'response_notes'              => 'Complessità tecnica per esportazione multipiattaforma. Notificata la proroga di 60 giorni ai sensi dell\'Art. 12.3 GDPR.',
                'identity_verified'           => true,
                'identity_verification_method'=> 'Firma Digitale CAdES/PAdES su istanza',
                'channel'                     => 'PEC',
            ],

            // ── 4. Diritto di Rettifica (Art. 16 GDPR) - Completata ──────────────
            [
                'company_id'                  => $company->id,
                'registrable_type'            => $client ? Client::class : null,
                'registrable_id'              => $client?->id,
                'requester_name'              => 'Chiara Neri',
                'requester_email'             => 'chiara.neri@example.com',
                'requester_phone'             => '+39 349 5566778',
                'request_type'                => 'rectification',
                'status'                      => 'completed',
                'received_at'                 => now()->subDays(18),
                'deadline_at'                 => now()->subDays(18)->addDays(30),
                'completed_at'                => now()->subDays(15),
                'request_description'         => 'Rettifica del recapito di residenza e aggiornamento numero telefonico aziendale.',
                'response_notes'              => 'Anagrafica aggiornata nei sistemi gestionali. Notificata la modifica a tutti i responsabili esterni coinvolti.',
                'identity_verified'           => true,
                'identity_verification_method'=> 'Email verificata tramite link OTP',
                'channel'                     => 'Portale Web',
            ],

            // ── 5. Opposizione al Trattamento (Art. 21 GDPR) - Rigettata ────────
            [
                'company_id'                  => $company->id,
                'registrable_type'            => null,
                'registrable_id'              => null,
                'requester_name'              => 'Antonio Bianchi',
                'requester_email'             => 'antonio.b@example.net',
                'requester_phone'             => '+39 338 6677889',
                'request_type'                => 'objection',
                'status'                      => 'rejected',
                'received_at'                 => now()->subDays(20),
                'deadline_at'                 => now()->subDays(20)->addDays(30),
                'completed_at'                => now()->subDays(10),
                'request_description'         => 'Opposizione alla conservazione delle fatture e dei dati contabili relativi a forniture pregresse.',
                'response_notes'              => 'Diniego formale motivato inviato all\'interessato.',
                'rejection_reason'            => 'I dati contabili e di fatturazione devono essere obbligatoriamente conservati per 10 anni ex Art. 2220 c.c. e normative fiscali.',
                'identity_verified'           => true,
                'identity_verification_method'=> 'Documento di identità verificato',
                'channel'                     => 'PEC',
            ],

            // ── 6. Revoca del Consenso (Art. 7.3 GDPR) - Nuova Ricevuta ─────────
            [
                'company_id'                  => $company->id,
                'registrable_type'            => null,
                'registrable_id'              => null,
                'requester_name'              => 'Sara Colombo',
                'requester_email'             => 'sara.colombo@example.com',
                'requester_phone'             => '+39 340 8899001',
                'request_type'                => 'withdraw_consent',
                'status'                      => 'received',
                'received_at'                 => now()->subDays(2),
                'deadline_at'                 => now()->subDays(2)->addDays(30),
                'completed_at'                => null,
                'request_description'         => 'Revoca del consenso prestato per attività di profilazione e newsletter settimanale.',
                'response_notes'              => null,
                'identity_verified'           => false,
                'identity_verification_method'=> null,
                'channel'                     => 'Email',
            ],
        ];

        foreach ($requests as $req) {
            DataSubjectRequest::create($req);
        }

        $this->command->info(count($requests).' Data Subject Requests (DSAR) seeded.');
    }
}
