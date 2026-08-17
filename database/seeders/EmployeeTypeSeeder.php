<?php

namespace Database\Seeders;

use App\Models\EmployeeType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('employee_types')->truncate();
        Schema::enableForeignKeyConstraints();

        $types = [
            // ── Ruoli interni ────────────────────────────────────────────────
            ['name' => 'Dipendente',            'icon' => 'heroicon-o-user',                'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Amministrativo',        'icon' => 'heroicon-o-briefcase',           'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Responsabile di Area',  'icon' => 'heroicon-o-user-circle',         'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Dirigente / Manager',   'icon' => 'heroicon-o-building-office',     'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'CdA / Socio',           'icon' => 'heroicon-o-star',                'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Segreteria / Back Office','icon' => 'heroicon-o-inbox',             'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Commerciale / Agente',  'icon' => 'heroicon-o-currency-euro',       'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Istruttore / Formatore','icon' => 'heroicon-o-academic-cap',        'companytype' => 'GENERAL', 'is_external' => false],

            // ── Ruoli Privacy & Compliance ───────────────────────────────────
            ['name' => 'DPO (Data Protection Officer)',         'icon' => 'heroicon-o-shield-check',        'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Titolare del Trattamento',             'icon' => 'heroicon-o-lock-closed',         'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Responsabile del Trattamento',         'icon' => 'heroicon-o-document-check',      'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Incaricato del Trattamento',           'icon' => 'heroicon-o-pencil-square',       'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Compliance Officer',                   'icon' => 'heroicon-o-clipboard-document-check','companytype' => 'GENERAL','is_external' => false],
            ['name' => 'Internal Auditor',                     'icon' => 'heroicon-o-magnifying-glass',    'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Responsabile della Sicurezza (CISO)',  'icon' => 'heroicon-o-shield-exclamation',  'companytype' => 'GENERAL', 'is_external' => false],
            ['name' => 'Amministratore di Sistema (AdS)',      'icon' => 'heroicon-o-server',              'companytype' => 'GENERAL', 'is_external' => false],

            // ── Ruoli settore Finance / AML ──────────────────────────────────
            ['name' => 'Responsabile SOS / AML',               'icon' => 'heroicon-o-exclamation-triangle','companytype' => 'FINANCE', 'is_external' => false],
            ['name' => 'Responsabile Qualità',                 'icon' => 'heroicon-o-check-badge',         'companytype' => 'FINANCE', 'is_external' => false],
            ['name' => 'Responsabile Reclami',                 'icon' => 'heroicon-o-chat-bubble-oval-left','companytype' => 'FINANCE','is_external' => false],
            ['name' => 'OAM (Operatore Antiriciclaggio)',       'icon' => 'heroicon-o-identification',     'companytype' => 'FINANCE', 'is_external' => false],
            ['name' => 'IVASS / RUI',                          'icon' => 'heroicon-o-document-text',       'companytype' => 'FINANCE', 'is_external' => false],
            ['name' => 'Mediatore Creditizio',                 'icon' => 'heroicon-o-currency-euro',       'companytype' => 'FINANCE', 'is_external' => false],

            // ── Figure esterne / collaboratori ───────────────────────────────
            ['name' => 'Consulente Esterno',            'icon' => 'heroicon-o-user-plus',    'companytype' => 'GENERAL', 'is_external' => true],
            ['name' => 'Avvocato / Legale Esterno',     'icon' => 'heroicon-o-scale',        'companytype' => 'GENERAL', 'is_external' => true],
            ['name' => 'Commercialista / Revisore',     'icon' => 'heroicon-o-calculator',   'companytype' => 'GENERAL', 'is_external' => true],
            ['name' => 'Partner / Sub-Agente',          'icon' => 'heroicon-o-link',         'companytype' => 'GENERAL', 'is_external' => true],
            ['name' => 'Tirocinante / Stagista',        'icon' => 'heroicon-o-academic-cap', 'companytype' => 'GENERAL', 'is_external' => true],
        ];

        foreach ($types as $type) {
            EmployeeType::create($type);
        }

        $this->command->info(count($types).' employee types seeded.');
    }
}
