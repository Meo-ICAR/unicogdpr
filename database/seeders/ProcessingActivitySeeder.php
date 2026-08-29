<?php

namespace Database\Seeders;

use App\Models\ClientController;
use App\Models\Company;
use App\Models\PrivacyDataType;
use App\Models\PrivacySecurity;
use App\Models\ProcessingActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessingActivitySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('processing_activity_privacy_security')->truncate();
        DB::table('processing_activity_privacy_data_type')->truncate();
        DB::table('processing_activities')->truncate();
        Schema::enableForeignKeyConstraints();

        $company    = Company::first();
        $controller = ClientController::where('is_active', true)->first();

        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo ProcessingActivitySeeder.');
            return;
        }

        // Recupera ID lookup per le relazioni BelongsToMany
        $dataTypeMap     = PrivacyDataType::pluck('id', 'name');
        $securityMap     = PrivacySecurity::pluck('id', 'name');

        // Helper per trovare ID da nomi parziali
        $dataTypes = fn (array $names) => $dataTypeMap
            ->filter(fn ($id, $name) => collect($names)->contains(fn ($n) => str_contains(strtolower($name), strtolower($n))))
            ->values()->toArray();

        $securities = fn (array $names) => $securityMap
            ->filter(fn ($id, $name) => collect($names)->contains(fn ($n) => str_contains(strtolower($name), strtolower($n))))
            ->values()->toArray();

        $activities = [

            // ── 1. Gestione Risorse Umane (Titolare) ──────────────────────────
            [
                'code'                       => 'TRATT-001',
                'name'                       => 'Gestione Risorse Umane e Paghe',
                'role'                       => 'controller',
                'client_controller_id'       => null,
                'purposes'                   => "• Adempimento del contratto di lavoro (stipendi, cedolini, CUD)\n"
                    ."• Gestione presenze, ferie e permessi\n"
                    ."• Adempimenti previdenziali (INPS, INAIL)\n"
                    .'• Formazione e sviluppo professionale',
                'legal_basis'                => "Art. 6.1.b GDPR – Esecuzione del contratto\nArt. 6.1.c GDPR – Obbligo legale (D.Lgs. 81/08, DPR 600/73)",
                'data_subject_categories'    => 'Dipendenti e collaboratori (tempo indeterminato, determinato, stage, consulenti)',
                'recipients'                 => "• Studio di consulenza del lavoro (Responsabile ex Art. 28)\n"
                    ."• INPS / INAIL (autorità pubbliche – Art. 6.1.c)\n"
                    .'• Istituto bancario per accredito stipendi',
                'has_third_country_transfers' => false,
                'third_countries_details'    => null,
                'retention_policy'           => '10 anni dalla cessazione del rapporto di lavoro (Art. 2220 c.c.)',
                'is_active'                  => true,
                'notes'                      => 'Dati particolari: certificati medici (Art. 9 GDPR) – base giuridica Art. 9.2.b.',
                'data_type_keys'             => ['anagrafico', 'fiscale', 'bancario', 'lavorativo'],
                'security_keys'             => ['crittografi', 'backup', 'accessi', 'mfa'],
            ],

            // ── 2. Gestione Clienti e Contratti (Titolare) ────────────────────
            [
                'code'                       => 'TRATT-002',
                'name'                       => 'Gestione Clienti, Contratti e Fatturazione',
                'role'                       => 'controller',
                'client_controller_id'       => null,
                'purposes'                   => "• Esecuzione del contratto di fornitura servizi\n"
                    ."• Fatturazione e adempimenti fiscali\n"
                    .'• Gestione assistenza post-vendita',
                'legal_basis'                => "Art. 6.1.b GDPR – Esecuzione del contratto\nArt. 6.1.c GDPR – Obbligo legale (D.Lgs. 127/15 SDI)",
                'data_subject_categories'    => 'Clienti persone fisiche e referenti aziendali',
                'recipients'                 => "• Studio commercialista (Responsabile ex Art. 28)\n"
                    ."• Sistema di Interscambio SDI – Agenzia delle Entrate\n"
                    .'• Eventuale legale in caso di contenzioso (interesse legittimo)',
                'has_third_country_transfers' => false,
                'third_countries_details'    => null,
                'retention_policy'           => '10 anni per documenti fiscali (Art. 2220 c.c.); 5 anni per corrispondenza (Art. 2948 c.c.)',
                'is_active'                  => true,
                'notes'                      => null,
                'data_type_keys'             => ['anagrafico', 'fiscale', 'contratto', 'contatto'],
                'security_keys'             => ['crittografi', 'backup', 'firewall'],
            ],

            // ── 3. Marketing Diretto (Titolare) ───────────────────────────────
            [
                'code'                       => 'TRATT-003',
                'name'                       => 'Marketing Diretto e Newsletter',
                'role'                       => 'controller',
                'client_controller_id'       => null,
                'purposes'                   => "• Invio newsletter e comunicazioni commerciali via email\n"
                    ."• Campagne SMS promozionali\n"
                    .'• Profilazione base per segmentazione offerta',
                'legal_basis'                => 'Art. 6.1.a GDPR – Consenso esplicito (opt-in documentato)',
                'data_subject_categories'    => 'Clienti e prospect che hanno prestato consenso marketing',
                'recipients'                 => "• MailSender Pro S.r.l. (Responsabile ex Art. 28 – piattaforma email)\n"
                    .'• Nessun altro destinatario',
                'has_third_country_transfers' => true,
                'third_countries_details'    => 'Trasferimento verso USA tramite piattaforma MailSender Pro (fornitore con certificazione DPF UE-USA attiva). TIA disponibile.',
                'retention_policy'           => 'Fino a revoca del consenso; massimo 36 mesi dall\'ultimo contatto attivo',
                'is_active'                  => true,
                'notes'                      => 'Gestione opt-out centralizzata nel modulo Opt-Out. Registro RPO verificato mensilmente.',
                'data_type_keys'             => ['anagrafico', 'contatto', 'comportament'],
                'security_keys'             => ['backup', 'accessi'],
            ],

            // ── 4. Attività per conto del Cliente (Responsabile – Art. 30.2) ──
            [
                'code'                       => 'TRATT-004',
                'name'                       => 'Raccolta Contratti per conto di '.($controller?->name ?? 'Mandante'),
                'role'                       => 'processor',
                'client_controller_id'       => $controller?->id,
                'purposes'                   => "• Raccolta e trasmissione contratti di fornitura per conto del Titolare (Cliente)\n"
                    ."• Gestione documenti di identità e moduli di consenso per attivazione servizi\n"
                    .'• Verifica antifrode e KYC per conto del Cliente',
                'legal_basis'                => "Istruzione del Titolare del Trattamento ex Art. 28.3 GDPR\nBase giuridica determinata dal Titolare: Art. 6.1.b (contratto) e Art. 6.1.c (obbligo legale AML)",
                'data_subject_categories'    => 'Utenti finali / Contraenti del Cliente (Mandante)',
                'recipients'                 => "• Cliente – Titolare del Trattamento (destinatario primario)\n"
                    ."• CRIF / SIA (verifica creditizia, su istruzione del Cliente)\n"
                    .'• Autorità competenti su richiesta obbligatoria',
                'has_third_country_transfers' => false,
                'third_countries_details'    => null,
                'retention_policy'           => 'Come da istruzioni del Cliente (Titolare). Di default: durata del contratto + 5 anni.',
                'is_active'                  => true,
                'notes'                      => 'DPA firmato con il Cliente. Operatori autorizzati elencati nel registro client_controller_employee.',
                'data_type_keys'             => ['anagrafico', 'identità', 'fiscale', 'bancario'],
                'security_keys'             => ['crittografi', 'accessi', 'mfa', 'backup'],
            ],

            // ── 5. Videosorveglianza (Titolare) ───────────────────────────────
            [
                'code'                       => 'TRATT-005',
                'name'                       => 'Sistema di Videosorveglianza Sede',
                'role'                       => 'controller',
                'client_controller_id'       => null,
                'purposes'                   => "• Sicurezza dei beni aziendali e delle persone presenti nei locali\n"
                    .'• Prevenzione e accertamento di illeciti (interesse legittimo prevalente)',
                'legal_basis'                => 'Art. 6.1.f GDPR – Legittimo interesse (sicurezza della sede)',
                'data_subject_categories'    => 'Dipendenti, fornitori, visitatori e clienti che accedono ai locali aziendali',
                'recipients'                 => "• Istituto di Vigilanza (Responsabile ex Art. 28)\n"
                    .'• Forze dell\'ordine su richiesta obbligatoria',
                'has_third_country_transfers' => false,
                'third_countries_details'    => null,
                'retention_policy'           => '24-48 ore per sovrascrittura automatica; 7 giorni in caso di incidente documentato',
                'is_active'                  => true,
                'notes'                      => 'Informativa ex Art. 13 GDPR esposta all\'ingresso. LIA (Legitimate Interest Assessment) disponibile.',
                'data_type_keys'             => ['immagin', 'biometr'],
                'security_keys'             => ['accessi', 'backup'],
            ],

        ];

        foreach ($activities as $data) {
            $dataTypeKeys  = $data['data_type_keys'];
            $securityKeys = $data['security_keys'];
            unset($data['data_type_keys'], $data['security_keys']);

            $activity = ProcessingActivity::create(
                array_merge($data, ['company_id' => $company->id])
            );

            // BelongsToMany: Categorie di dati
            $dtIds = $dataTypes($dataTypeKeys);
            if (! empty($dtIds)) {
                $activity->privacyDataTypes()->syncWithoutDetaching($dtIds);
            }

            // BelongsToMany: Misure di sicurezza
            $secIds = $securities($securityKeys);
            if (! empty($secIds)) {
                $activity->privacySecurities()->syncWithoutDetaching($secIds);
            }
        }

        $this->command->info(count($activities).' processing activities seeded.');
    }
}
