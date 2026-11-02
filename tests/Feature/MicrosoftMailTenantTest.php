<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\MailAccount;
use App\Services\Mail\MicrosoftMailOAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MicrosoftMailTenantTest extends TestCase
{
    use RefreshDatabase;

    private const TENANT = 'c2173c83-0a4b-4c63-b6c5-a11286d392e1';

    public function test_il_tenant_dell_azienda_ha_la_precedenza_e_finisce_nel_link_di_consenso(): void
    {
        Http::fake();
        $company = Company::factory()->create(['microsoft_tenant_id' => self::TENANT]);
        $account = MailAccount::factory()->create(['company_id' => $company->id, 'email_address' => 'privacy@example.it']);

        $url = app(MicrosoftMailOAuth::class)->adminConsentUrl($account);

        $this->assertStringContainsString('/'.self::TENANT.'/v2.0/adminconsent', $url);
        Http::assertNothingSent();
    }

    public function test_se_vuoto_il_tenant_si_ricava_dal_dominio_e_si_memorizza(): void
    {
        Http::fake(['login.microsoftonline.com/*' => Http::response([
            'token_endpoint' => 'https://login.microsoftonline.com/'.self::TENANT.'/oauth2/v2.0/token',
        ])]);
        $company = Company::factory()->create(['microsoft_tenant_id' => null]);
        $account = MailAccount::factory()->create(['company_id' => $company->id, 'email_address' => 'privacy@team2com.it']);

        $this->assertSame(self::TENANT, app(MicrosoftMailOAuth::class)->tenant($account));
        $this->assertSame(self::TENANT, $company->fresh()->microsoft_tenant_id);
    }

    public function test_dominio_non_riconosciuto_ripiega_sulla_configurazione(): void
    {
        Http::fake(['login.microsoftonline.com/*' => Http::response([], 400)]);
        config(['services.microsoft.mail_tenant' => 'fallback.onmicrosoft.com']);
        $company = Company::factory()->create(['microsoft_tenant_id' => null]);
        $account = MailAccount::factory()->create(['company_id' => $company->id, 'email_address' => 'privacy@nessuno.example']);

        $this->assertSame('fallback.onmicrosoft.com', app(MicrosoftMailOAuth::class)->tenant($account));
        $this->assertNull($company->fresh()->microsoft_tenant_id);
    }
}
