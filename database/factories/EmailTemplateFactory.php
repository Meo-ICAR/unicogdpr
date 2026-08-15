<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    protected $model = EmailTemplate::class;

    public function definition(): array
    {
        return [
            'company_id'   => Company::factory(),
            'code'         => 'dsar_generic_'.fake()->unique()->slug(2),
            'name'         => 'Modello Risposta DSAR Standard',
            'subject'      => 'Riscontro alla richiesta di esercizio diritti GDPR - {company_name}',
            'body_html'    => '<p>Gentile <strong>{requester_name}</strong>,</p><p>con riferimento alla Sua richiesta ricevuta in data <strong>{received_at}</strong> avente ad oggetto l\'esercizio del diritto di <em>{request_type}</em> ai sensi del Regolamento UE 2016/679 (GDPR), Le comunichiamo quanto segue.</p><p>Cordiali saluti,<br>Ufficio Privacy - {company_name}</p>',
            'body_text'    => "Gentile {requester_name},\n\nCon riferimento alla Sua richiesta ricevuta il {received_at} (tipo: {request_type}), Le comunichiamo che la pratica è stata elaborata.\n\nCordiali saluti,\nUfficio Privacy {company_name}",
            'placeholders' => ['{requester_name}', '{received_at}', '{deadline_at}', '{request_type}', '{company_name}'],
            'is_active'    => true,
        ];
    }
}
