<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            ['name' => 'RACES FINANCE S.R.L.', 'vat_number' => '10282211001'],

            ['name' => 'GBD ENERGY S.R.L.', 'vat_number' => '10175191211'],
          ['name' => 'PHOENIX2PEOPLE S.R.L.', 'vat_number' => '10862541215'],
      
            ['name' => 'PHOENIX2VALUE SOCIETA\' A RESPONSABILITA\' LIMITATA SEMPLIFICATA', 'vat_number' => '02127630438'],
                     ['name' => 'THUNDER S.R.L.', 'vat_number' => '08865331212'],

            ['name' => 'PALK S.R.L.', 'vat_number' => '09816371216'],
            ['name' => 'PEOPLE GROUP S.R.L.', 'vat_number' => '08719101217'],
            ['name' => 'NO&MI S.R.L.', 'vat_number' => '02910060355'],
            ['name' => 'PEOPLE S.R.L.', 'vat_number' => '08357181216'],
               ['name' => 'PROFESSIONE CREDITO AGENZIA IN ATTIVITA\' FINANZIARIA S.R.L.', 'vat_number' => '09926620965'],
            ['name' => 'TEAM2COM - SOCIETA\' A RESPONSABILITA\' LIMITATA SEMPLIFICATA', 'vat_number' => '14717001003'],
         
            ['name' => 'MR SOCIETA\' A RESPONSABILITA\' LIMITATA SEMPLIFICATA', 'vat_number' => '04541290617'],
   ['name' => 'Lead2Com Ltd', 'vat_number' => 'GB470921785'],
        
            ['name' => 'DIGITAL REV GROUP LTD', 'vat_number' => '13535669'],
           
            ];

        foreach ($companies as $companyData) {
            Company::updateOrCreate(
                ['vat_number' => $companyData['vat_number']], // Condizione di ricerca
                [
                    'name' => $companyData['name'],
                    // Se utilizzi gli UUID nel model, Laravel lo gestirà in automatico nel creating,
                    // in alternativa puoi forzarlo decommentando la riga qui sotto:
                    // 'id' => (string) Str::uuid(), 
                ]
            );
        }
    }
}