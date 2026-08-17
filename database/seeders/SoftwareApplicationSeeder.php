<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SoftwareApplication;
use App\Models\SoftwareCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SoftwareApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            $this->command->warn('Nessuna company trovata. Skippo SoftwareApplicationSeeder.');
            return;
        }

        $catMap = SoftwareCategory::pluck('id', 'code')->toArray();

        $applications = [
            [
                'name'                 => 'HubSpot CRM',
                'category'             => 'CRM',
                'provider_name'        => 'HubSpot Inc.',
                'website_url'          => 'https://www.hubspot.com',
                'api_url'              => 'https://api.hubapi.com',
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Zucchetti HR Infinity',
                'category'             => 'HR',
                'provider_name'        => 'Zucchetti S.p.A.',
                'website_url'          => 'https://www.zucchetti.it',
                'api_url'              => null,
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Microsoft 365 (Exchange + Teams)',
                'category'             => 'MAIL',
                'provider_name'        => 'Microsoft Corporation',
                'website_url'          => 'https://www.microsoft.com/it-it/microsoft-365',
                'api_url'              => 'https://graph.microsoft.com',
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Mailchimp',
                'category'             => 'MKT',
                'provider_name'        => 'The Rocket Science Group LLC (Intuit)',
                'website_url'          => 'https://mailchimp.com',
                'api_url'              => 'https://us1.api.mailchimp.com/3.0',
                'is_cloud'             => true,
                'is_data_eu'           => false,
                'is_iso27001_certified'=> false,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Google Workspace (Drive + Meet)',
                'category'             => 'CLOUD',
                'provider_name'        => 'Google LLC',
                'website_url'          => 'https://workspace.google.com',
                'api_url'              => 'https://www.googleapis.com',
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Veeam Backup & Replication',
                'category'             => 'BDR',
                'provider_name'        => 'Veeam Software',
                'website_url'          => 'https://www.veeam.com',
                'api_url'              => null,
                'is_cloud'             => false,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Kaspersky Endpoint Security',
                'category'             => 'SEC',
                'provider_name'        => 'Kaspersky Lab',
                'website_url'          => 'https://www.kaspersky.it',
                'api_url'              => null,
                'is_cloud'             => false,
                'is_data_eu'           => false,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'DocuSign',
                'category'             => 'SIGN',
                'provider_name'        => 'DocuSign Inc.',
                'website_url'          => 'https://www.docusign.com',
                'api_url'              => 'https://www.docusign.net/restapi',
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 45.00,
            ],
            [
                'name'                 => 'Zendesk Support',
                'category'             => 'HELP',
                'provider_name'        => 'Zendesk Inc.',
                'website_url'          => 'https://www.zendesk.com',
                'api_url'              => 'https://subdomain.zendesk.com/api/v2',
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
            [
                'name'                 => 'Power BI',
                'category'             => 'BI',
                'provider_name'        => 'Microsoft Corporation',
                'website_url'          => 'https://powerbi.microsoft.com',
                'api_url'              => 'https://api.powerbi.com',
                'is_cloud'             => true,
                'is_data_eu'           => true,
                'is_iso27001_certified'=> true,
                'wallet_balance'       => 0,
            ],
        ];

        foreach ($applications as $data) {
            $categoryCode = $data['category'];
            unset($data['category']);

            SoftwareApplication::firstOrCreate(
                ['name' => $data['name'], 'company_id' => $company->id],
                array_merge($data, [
                    'company_id'           => $company->id,
                    'software_category_id' => $catMap[$categoryCode] ?? null,
                ])
            );
        }

        $this->command->info(count($applications).' software applications seeded.');
    }
}
