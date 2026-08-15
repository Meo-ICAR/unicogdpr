<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\DataBreach;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DataBreach>
 */
class DataBreachFactory extends Factory
{
    protected $model = DataBreach::class;

    public function definition(): array
    {
        $severities = ['low', 'medium', 'high'];
        $severity = fake()->randomElement($severities);
        $statuses = ['investigating', 'contained', 'resolved', 'notified'];

        return [
            'company_id'                => Company::factory(),
            'name'                      => fake()->randomElement([
                'Attacco Phishing su Casella Email Amministrazione',
                'Accesso non autorizzato a Database Clienti CRM',
                'Smarrimento Laptop Aziendale non Cifrato',
                'Esposizione involontaria Backup Cloud su Bucket Pubblico',
                'Infezione Ransomware su Server File Condivisi',
            ]),
            'discovered_at'             => fake()->dateTimeBetween('-10 days', 'now'),
            'occurred_at'               => fake()->dateTimeBetween('-15 days', '-10 days'),
            'description'               => fake()->paragraph(3),
            'nature_of_breach'          => fake()->randomElement(['confidentiality', 'integrity', 'availability', 'combined']),
            'approximate_records_count' => fake()->numberBetween(50, 15000),
            'severity'                  => $severity,
            'status'                    => fake()->randomElement($statuses),
            'affected_data_categories'  => 'Dati anagrafici, indirizzi email, credenziali di accesso, coordinate IBAN',
            'affected_individuals'      => 'Clienti consumer e personale dipendente',
            'root_cause'                => 'Credenziali compromesse tramite campagna di social engineering',
            'corrective_actions'        => 'Reset immediato di tutte le password, isolamento dell\'host infetto, revoca sessioni attive',
            'preventive_measures'       => 'Attivazione MFA obbligatoria per tutti gli account aziendali, sessione di security awareness',
            'is_notifiable_to_authority'=> in_array($severity, ['medium', 'high']),
            'is_notifiable_to_subjects' => $severity === 'high',
            'mitigation_actions'        => 'Contatto tempestivo con i soggetti coinvolti e monitoraggio log firewall',
        ];
    }

    public function highSeverity(): static
    {
        return $this->state(fn (array $attributes) => [
            'severity'                   => 'high',
            'status'                     => 'investigating',
            'is_notifiable_to_authority' => true,
            'is_notifiable_to_subjects'  => true,
            'approximate_records_count'  => 5000,
        ]);
    }
}
