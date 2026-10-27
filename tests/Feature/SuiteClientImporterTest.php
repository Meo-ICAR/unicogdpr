<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use App\Models\Company;
use App\Models\Suite\SuiteCompany;
use App\Models\User;
use App\Services\SuiteImport\ClientImporter;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Livewire\Livewire;
use RuntimeException;
use Tests\Support\SuiteFixtures;
use Tests\TestCase;

class SuiteClientImporterTest extends TestCase
{
    use RefreshDatabase;
    use SuiteFixtures;

    private Company $local;

    private SuiteCompany $suite;

    private int $suiteCompanyId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suiteTruncate();

        $this->suiteCompanyId = $this->suiteInsert('companies', ['name' => 'Races Finance S.r.l.', 'vat_number' => '10282211001']);
        $this->suite = SuiteCompany::query()->findOrFail($this->suiteCompanyId);
        $this->local = Company::factory()->create(['vat_number' => '10282211001']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function suiteClient(array $overrides = []): int
    {
        return $this->suiteInsert('clients', array_merge([
            'company_id' => $this->suiteCompanyId,
            'name' => 'Mario Rossi',
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
            'tax_code' => 'RSSMRA80A01H501U',
            'email' => 'mario@example.test',
            'is_person' => 1,
            'is_company' => 0,
            'subject_type' => 'person',
            'is_pep' => 0,
            'is_sanctioned' => 0,
            'is_remote_interaction' => 0,
            'status' => 'ATTIVO',
            'is_lead' => 0,
        ], $overrides));
    }

    private function importOne(string $key): Client
    {
        return (new ClientImporter)->importOne($key, $this->local, $this->suite);
    }

    public function test_imports_client_by_suite_id(): void
    {
        $id = $this->suiteClient(['phone' => '0612345678', 'city' => 'Roma', 'country' => 'IT']);

        $client = $this->importOne((string) $id);

        $this->assertSame($this->local->id, $client->company_id);
        $this->assertSame('person', $client->subject_type);
        $this->assertSame('Mario Rossi', $client->name);
        $this->assertSame('Mario', $client->first_name);
        $this->assertSame('RSSMRA80A01H501U', $client->tax_code);
        $this->assertSame('0612345678', $client->phone);
        $this->assertSame('IT', $client->country);
        $this->assertNull($client->client_type_id);
        $this->assertSame('unicosuite:clients', $client->source_system);
        $this->assertSame((string) $id, $client->source_id);
    }

    public function test_imports_client_by_tax_code_case_insensitive(): void
    {
        $id = $this->suiteClient();

        $client = $this->importOne(' rssmra80a01h501u ');

        $this->assertSame((string) $id, $client->source_id);
    }

    public function test_imports_company_by_vat_number_and_maps_subject_type(): void
    {
        $id = $this->suiteClient([
            'name' => 'Alfa S.r.l.', 'first_name' => null, 'last_name' => null, 'tax_code' => null,
            'vat_number' => '01234567890', 'is_company' => 1, 'is_person' => 0, 'subject_type' => 'company',
        ]);

        $client = $this->importOne('01234567890');

        $this->assertSame('company', $client->subject_type);
        $this->assertSame((string) $id, $client->source_id);
    }

    public function test_eleven_digit_key_is_a_vat_number_not_an_id(): void
    {
        $this->suiteClient(['tax_code' => null, 'vat_number' => '01234567890', 'is_company' => 1]);

        $this->assertSame('01234567890', $this->importOne('01234567890')->vat_number);

        $this->expectException(ModelNotFoundException::class);
        // Un valore di 11 cifre non e' mai un id suite.
        $this->importOne('00000000001');
    }

    public function test_second_import_updates_same_record(): void
    {
        $id = $this->suiteClient();
        $first = $this->importOne((string) $id);

        $this->assertFalse($first->wasChanged());

        DB::connection('unicosuite')->table('clients')->where('id', $id)->update(['city' => 'Milano']);

        $second = $this->importOne((string) $id);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('Milano', $second->city);
        $this->assertSame(1, Client::query()->count());

        $third = $this->importOne((string) $id);
        $this->assertFalse($third->wasChanged());
        $this->assertSame(1, Client::query()->count());
    }

    public function test_adopts_local_client_with_same_tax_code(): void
    {
        $id = $this->suiteClient(['phone' => null]);
        $existing = Client::query()->create([
            'company_id' => $this->local->id,
            'subject_type' => 'person',
            'name' => 'Mario Rossi (manuale)',
            'tax_code' => 'RSSMRA80A01H501U',
            'phone' => '333',
        ]);

        $client = $this->importOne((string) $id);

        $this->assertSame($existing->id, $client->id);
        $this->assertSame(1, Client::query()->count());
        $this->assertSame((string) $id, $client->source_id);
        $this->assertSame('333', $client->fresh()->phone);
        $this->assertSame('Mario Rossi', $client->fresh()->name);
    }

    public function test_does_not_adopt_client_of_another_company_or_with_empty_keys(): void
    {
        $other = Company::factory()->create();
        Client::query()->create(['company_id' => $other->id, 'subject_type' => 'person', 'name' => 'Altro', 'tax_code' => 'RSSMRA80A01H501U']);
        Client::query()->create(['company_id' => $this->local->id, 'subject_type' => 'person', 'name' => 'Senza codici']);

        $id = $this->suiteClient(['tax_code' => null, 'vat_number' => null, 'email' => 'x@example.test']);

        $client = $this->importOne((string) $id);

        $this->assertSame((string) $id, $client->source_id);
        $this->assertSame(3, Client::query()->count());
    }

    public function test_overflowing_sdi_code_is_truncated_and_country_normalised(): void
    {
        $id = $this->suiteClient([
            'sdi_code' => 'ABCDEFG123', 'country' => 'it',
        ]);

        $client = $this->importOne((string) $id);

        $this->assertSame('ABCDEFG', $client->sdi_code);
        $this->assertSame('IT', $client->country);
    }

    public function test_unknown_key_throws_without_leaking_the_key(): void
    {
        try {
            $this->importOne('ZZZZZZ00A00A000A');
            $this->fail('Attesa ModelNotFoundException');
        } catch (ModelNotFoundException $exception) {
            $this->assertStringNotContainsString('ZZZZZZ00A00A000A', $exception->getMessage());
        }
    }

    public function test_ambiguous_key_is_rejected(): void
    {
        $this->suiteClient(['tax_code' => 'AAAAAA00A00A000A']);
        $this->suiteClient(['tax_code' => null, 'vat_number' => 'AAAAAA00A00A000A']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('più clienti');
        $this->importOne('AAAAAA00A00A000A');
    }

    public function test_client_of_another_suite_company_is_not_found(): void
    {
        $otherCompanyId = $this->suiteInsert('companies', ['name' => 'Altra', 'vat_number' => '99999999999']);
        $id = $this->suiteClient(['company_id' => $otherCompanyId]);

        $this->expectException(ModelNotFoundException::class);
        $this->importOne((string) $id);
    }

    public function test_deleted_or_anonymized_suite_clients_are_rejected(): void
    {
        $deleted = $this->suiteClient(['deleted_at' => now()]);
        $anonymized = $this->suiteClient(['anonymized_at' => now(), 'tax_code' => 'BBBBBB00A00A000B']);

        foreach ([(string) $deleted, (string) $anonymized] as $key) {
            try {
                $this->importOne($key);
                $this->fail('Attesa ModelNotFoundException');
            } catch (ModelNotFoundException) {
                $this->assertSame(0, Client::query()->count());
            }
        }

        $this->expectException(ModelNotFoundException::class);
        $this->importOne('BBBBBB00A00A000B');
    }

    public function test_soft_deleted_local_client_is_not_resurrected(): void
    {
        $id = $this->suiteClient();
        $client = $this->importOne((string) $id);
        $client->delete();

        try {
            $this->importOne((string) $id);
            $this->fail('Attesa RuntimeException');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString((string) $client->id, $exception->getMessage());
            $this->assertStringNotContainsString('Rossi', $exception->getMessage());
        }

        $this->assertSame(1, Client::query()->withTrashed()->count());
        $this->assertTrue(Client::query()->withTrashed()->first()->trashed());
    }

    public function test_command_imports_client(): void
    {
        $id = $this->suiteClient();

        $this->assertSame(0, Artisan::call('suite:import-client', ['key' => (string) $id]));

        $client = Client::query()->firstOrFail();
        $this->assertSame("Cliente importato (id locale {$client->id})", trim(Artisan::output()));
        $this->assertSame(1, Client::query()->count());
    }

    public function test_command_uses_company_vat_option(): void
    {
        $otherSuiteId = $this->suiteInsert('companies', ['name' => 'Altra', 'vat_number' => '12345678901']);
        $otherLocal = Company::factory()->create(['vat_number' => '12345678901']);
        $id = $this->suiteClient(['company_id' => $otherSuiteId]);

        $this->artisan('suite:import-client', ['key' => (string) $id, '--company-vat' => '12345678901'])
            ->assertSuccessful();

        $this->assertSame($otherLocal->id, Client::query()->firstOrFail()->company_id);
    }

    public function test_command_fails_with_italian_message_for_unknown_key(): void
    {
        $this->artisan('suite:import-client', ['key' => '999999'])
            ->expectsOutputToContain('Nessun cliente')
            ->assertFailed();
    }

    public function test_command_fails_for_unresolvable_company(): void
    {
        $this->artisan('suite:import-client', ['key' => '1', '--company-vat' => '00000000000'])
            ->expectsOutputToContain('assente')
            ->assertFailed();
    }

    private function actAsTenantUser(Company $tenant): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $user->companies()->attach($tenant, ['role' => 'dpo']);
        $this->actingAs($user);

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        ClientResource::registerTenancyModelGlobalScope($panel);
        Filament::setTenant($tenant);
    }

    public function test_list_clients_action_imports_client_into_current_tenant(): void
    {
        $id = $this->suiteClient();
        $this->actAsTenantUser($this->local);

        Livewire::test(ListClients::class)
            ->callAction(TestAction::make('importFromSuite'), ['key' => (string) $id])
            ->assertNotified('Cliente importato');

        $this->assertSame(1, Client::query()->where('company_id', $this->local->id)->count());
    }

    public function test_list_clients_action_notifies_error_for_unknown_key(): void
    {
        $this->actAsTenantUser($this->local);

        Livewire::test(ListClients::class)
            ->callAction(TestAction::make('importFromSuite'), ['key' => '999999'])
            ->assertNotified('Importazione non riuscita');

        $this->assertSame(0, Client::query()->count());
    }

    public function test_list_clients_action_requires_key_and_a_resolvable_company(): void
    {
        $this->actAsTenantUser($this->local);

        Livewire::test(ListClients::class)
            ->callAction(TestAction::make('importFromSuite'), ['key' => ''])
            ->assertHasActionErrors(['key' => 'required']);

        $withoutVat = Company::factory()->create(['vat_number' => null]);
        $this->actAsTenantUser($withoutVat);

        Livewire::test(ListClients::class)
            ->callAction(TestAction::make('importFromSuite'), ['key' => '1'])
            ->assertNotified('Importazione non riuscita');
    }
}
