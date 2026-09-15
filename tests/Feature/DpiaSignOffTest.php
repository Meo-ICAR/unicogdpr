<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Dpia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DpiaSignOffTest extends TestCase
{
    use RefreshDatabase;

    private function dpia(array $attributes = []): Dpia
    {
        return Dpia::create(array_merge([
            'company_id' => Company::factory()->create()->id,
            'name' => 'DPIA di test',
            'status' => 'draft',
        ], $attributes));
    }

    public function test_sign_off_fails_when_minimum_requirements_are_missing(): void
    {
        $dpia = $this->dpia();
        $dpo = User::factory()->create();

        $this->expectException(RuntimeException::class);

        $dpia->signOffByDpo($dpo);
    }

    public function test_sign_off_completes_dpia_and_computes_hash(): void
    {
        $dpia = $this->dpia([
            'description_of_processing' => 'Trattamento di test',
            'necessity_assessment' => 'Necessario e proporzionato',
            'dpo_opinion' => 'Parere favorevole',
        ]);
        $dpia->addRiskItem([
            'risk_source' => 'Accesso non autorizzato',
            'potential_impact' => 'Divulgazione dati',
            'probability' => 2,
            'severity' => 3,
        ]);
        $dpo = User::factory()->create();

        $dpia->signOffByDpo($dpo);
        $dpia->refresh();

        $this->assertSame('completed', $dpia->status);
        $this->assertTrue($dpia->isSignedByDpo());
        $this->assertSame($dpo->id, $dpia->dpo_signed_by);
        $this->assertNotNull($dpia->dpo_signature_hash);
        $this->assertSame(64, strlen($dpia->dpo_signature_hash));
    }

    public function test_content_hash_changes_when_signed_content_is_altered(): void
    {
        $dpia = $this->dpia([
            'description_of_processing' => 'Trattamento di test',
            'necessity_assessment' => 'Necessario e proporzionato',
            'dpo_opinion' => 'Parere favorevole',
        ]);
        $dpia->addRiskItem([
            'risk_source' => 'Accesso non autorizzato',
            'potential_impact' => 'Divulgazione dati',
            'probability' => 2,
            'severity' => 3,
        ]);

        $hashBefore = $dpia->computeContentHash();
        $dpia->dpo_opinion = 'Parere modificato dopo il calcolo';
        $hashAfter = $dpia->computeContentHash();

        $this->assertNotSame($hashBefore, $hashAfter);
    }
}
