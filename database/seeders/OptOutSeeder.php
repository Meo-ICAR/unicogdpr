<?php

namespace Database\Seeders;

use App\Models\ClientController;
use App\Models\OptOut;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OptOutSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('opt_outs')->truncate();
        Schema::enableForeignKeyConstraints();

        // Recupera il primo ClientController per le opposizioni specifiche per commessa
        $controller = ClientController::first();

        $records = [
            // ── Opposizioni Globali (client_controller_id = NULL) ─────────────
            [
                'phone'                => '+39 333 1234567',
                'email'                => 'mario.rossi.optout@example.com',
                'fiscal_code'          => 'RSSMRA70A01H501X',
                'channel'              => 'all',
                'source'               => 'direct_request',
                'client_controller_id' => null,
                'opt_out_at'           => now()->subMonths(8),
                'notes'                => 'Interessato ha revocato ogni consenso marketing. Opposizione registrata via PEC.',
            ],
            [
                'phone'                => '+39 347 9876543',
                'email'                => 'giulia.bianchi.optout@example.com',
                'fiscal_code'          => 'BNCGLI85B45H501Y',
                'channel'              => 'email',
                'source'               => 'dsar',
                'client_controller_id' => null,
                'opt_out_at'           => now()->subMonths(5),
                'notes'                => 'Opposizione al solo canale email. Contatti telefonici ancora attivi.',
            ],
            [
                'phone'                => '+39 320 5551234',
                'email'                => null,
                'fiscal_code'          => null,
                'channel'              => 'sms',
                'source'               => 'client_request',
                'client_controller_id' => null,
                'opt_out_at'           => now()->subMonths(3),
                'notes'                => 'Blocco solo SMS. Pervenuto tramite modulo sul sito.',
            ],
            [
                'phone'                => '+39 333 0001111',
                'email'                => 'opposizione.telefono@example.com',
                'fiscal_code'          => 'VRDLCA90D15H501Z',
                'channel'              => 'phone',
                'source'               => 'rpo',
                'client_controller_id' => null,
                'opt_out_at'           => now()->subMonths(12),
                'notes'                => 'Opposizione ricevuta dal Registro Pubblico delle Opposizioni (RPO).',
            ],
            [
                'phone'                => '+39 340 2223334',
                'email'                => 'anna.verdi.optout@example.com',
                'fiscal_code'          => 'VRDNNA65C41H501W',
                'channel'              => 'all',
                'source'               => 'direct_request',
                'client_controller_id' => null,
                'opt_out_at'           => now()->subDays(15),
                'notes'                => 'Opposizione globale. Verificata identità tramite documento allegato.',
            ],

            // ── Opposizioni Specifiche per Commessa (client_controller_id valorizzato) ──
            [
                'phone'                => '+39 346 5556667',
                'email'                => 'luca.neri.commessa@example.com',
                'fiscal_code'          => 'NRILCU88E10H501V',
                'channel'              => 'all',
                'source'               => 'direct_request',
                'client_controller_id' => $controller?->id,
                'opt_out_at'           => now()->subMonths(2),
                'notes'                => 'Opposizione specifica per la commessa '
                    .($controller?->name ?? 'N/A')
                    .'. Non bloccare per altri mandanti.',
            ],
            [
                'phone'                => '+39 349 7778889',
                'email'                => null,
                'fiscal_code'          => 'ESPSFN75F20H501U',
                'channel'              => 'phone',
                'source'               => 'client_request',
                'client_controller_id' => $controller?->id,
                'opt_out_at'           => now()->subMonths(1),
                'notes'                => 'Il cliente ha richiesto la revoca per questo nominativo. Solo canale telefonico.',
            ],

            // ── Record soft-deleted (storico) ─────────────────────────────────
            [
                'phone'                => '+39 328 9990001',
                'email'                => 'storico.optout@example.com',
                'fiscal_code'          => 'STRMRA60A01H501T',
                'channel'              => 'all',
                'source'               => 'direct_request',
                'client_controller_id' => null,
                'opt_out_at'           => now()->subYear(),
                'notes'                => 'Revocato dopo richiesta scritta dell\'interessato di riprendere le comunicazioni.',
                'deleted_at'           => now()->subMonths(6),
            ],
        ];

        foreach ($records as $record) {
            OptOut::create($record);
        }

        $this->command->info(count($records).' opt-out records seeded ('.
            collect($records)->whereNull('deleted_at')->count().' attivi, '.
            collect($records)->whereNotNull('deleted_at')->count().' archiviati).');
    }
}
