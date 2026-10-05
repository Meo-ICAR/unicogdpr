<?php
namespace Database\Seeders;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('clients')->truncate();
        Schema::enableForeignKeyConstraints();
        $company = Company::first();
        if (!$company) { $this->command->warn('Skip ClientSeeder: no company.'); return; }
        $typeIds = ClientType::pluck('id', 'name')->toArray();
        $b2cId = null; $b2bId = null;
        foreach ($typeIds as $name => $id) {
            if (str_contains($name, 'B2C')) { $b2cId = $id; }
            if (str_contains($name, 'B2B')) { $b2bId = $id; }
        }
        $b2cId = $b2cId ?? array_values($typeIds)[0] ?? null;
        $b2bId = $b2bId ?? array_values($typeIds)[0] ?? null;
        $rows = [
            ['subject_type'=>'person','client_type_id'=>$b2cId,'name'=>'Mario Rossi','first_name'=>'Mario','last_name'=>'Rossi','tax_code'=>'RSSMRA78A01H501U','email'=>'mario.rossi@example.com','phone'=>'+39 333 1234567','city'=>'Roma','country'=>'IT'],
            ['subject_type'=>'person','client_type_id'=>$b2cId,'name'=>'Giulia Bianchi','first_name'=>'Giulia','last_name'=>'Bianchi','tax_code'=>'BNCGLI85B45H501Y','email'=>'giulia.bianchi@example.com','phone'=>'+39 347 9876543','city'=>'Milano','country'=>'IT'],
            ['subject_type'=>'person','client_type_id'=>$b2cId,'name'=>'Luca Ferrari','first_name'=>'Luca','last_name'=>'Ferrari','tax_code'=>'FRRLCU88E10H501V','email'=>'luca.ferrari@example.com','city'=>'Torino','country'=>'IT'],
            ['subject_type'=>'company','client_type_id'=>$b2bId,'name'=>'Acme Solutions S.r.l.','vat_number'=>'12344321001','email'=>'info@acmesolutions.it','pec'=>'acme@pec.it','sdi_code'=>'XXXXXXX','city'=>'Bologna','country'=>'IT'],
            ['subject_type'=>'company','client_type_id'=>$b2bId,'name'=>'Beta Servizi S.p.A.','vat_number'=>'98877665500','email'=>'privacy@betaservizi.it','pec'=>'betaservizi@legalmail.it','sdi_code'=>'YYYYYYY','city'=>'Napoli','country'=>'IT'],
        ];
        foreach ($rows as $row) { Client::create(array_merge($row, ['company_id' => $company->id])); }
        $this->command->info(count($rows).' clients seeded.');
    }
}
