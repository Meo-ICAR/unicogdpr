<?php

namespace App\Services\SuiteImport;

use App\Models\Client;
use App\Models\Company;
use App\Models\Suite\SuiteClient;
use App\Models\Suite\SuiteCompany;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use RuntimeException;

class ClientImporter
{
    public const SOURCE_SYSTEM = 'unicosuite:clients';

    /** Sotto questa lunghezza un valore di sole cifre e' un id suite; 11 cifre sono una partita IVA. */
    private const MAX_SUITE_ID_LENGTH = 10;

    /**
     * Importa un singolo cliente della suite nella societa' locale indicata.
     *
     * `$key` e' un id suite (solo cifre, meno di 11) oppure un codice fiscale / partita IVA.
     * Dati di interessati: i messaggi riportano solo identificativi, mai nomi o codici.
     *
     * @throws ModelNotFoundException se nessun cliente attivo e non anonimizzato corrisponde nella societa'
     * @throws InvalidArgumentException se la chiave e' vuota o corrisponde a piu' clienti
     * @throws RuntimeException se il cliente locale collegato e' stato eliminato o anonimizzato
     */
    public function importOne(string $key, Company $local, SuiteCompany $suite): Client
    {
        $row = $this->findSuiteClient(trim($key), $suite);

        $attributes = $this->attributesFrom($row, $local);

        // `country` e' NOT NULL con default 'IT': un valore vuoto in suite non deve mai arrivare come NULL.
        if ($attributes['country'] === null) {
            unset($attributes['country']);
        }

        $existing = Client::query()
            ->withTrashed()
            ->where('source_system', self::SOURCE_SYSTEM)
            ->where('source_id', (string) $row->id)
            ->first();

        if ($existing !== null) {
            $this->assertImportable($existing, $local);

            return $this->update($existing, $attributes);
        }

        $adoptable = $this->findAdoptable($local, $attributes);

        if ($adoptable !== null) {
            // Un cliente inserito a mano non perde i propri valori a favore di un vuoto della suite.
            return $this->update($adoptable, array_filter($attributes, fn (mixed $value): bool => $value !== null) + [
                'source_system' => self::SOURCE_SYSTEM,
                'source_id' => (string) $row->id,
            ]);
        }

        return Client::query()->create($attributes + [
            'source_system' => self::SOURCE_SYSTEM,
            'source_id' => (string) $row->id,
        ]);
    }

    private function findSuiteClient(string $key, SuiteCompany $suite): SuiteClient
    {
        if ($key === '') {
            throw new InvalidArgumentException('Indicare un id suite, un codice fiscale o una partita IVA.');
        }

        $query = SuiteClient::query()
            ->where('company_id', $suite->id)
            ->whereNull('deleted_at')
            ->whereNull('anonymized_at');

        if (ctype_digit($key) && strlen($key) <= self::MAX_SUITE_ID_LENGTH) {
            $query->where('id', (int) $key);
        } else {
            $normalised = mb_strtolower($key);

            $query->where(function ($query) use ($normalised): void {
                $query->whereRaw('LOWER(TRIM(tax_code)) = ?', [$normalised])
                    ->orWhereRaw('LOWER(TRIM(vat_number)) = ?', [$normalised]);
            });
        }

        $matches = $query->orderBy('id')->limit(2)->get();

        if ($matches->count() > 1) {
            throw new InvalidArgumentException("La chiave corrisponde a più clienti nella società suite {$suite->id}: usare l'id suite.");
        }

        if ($matches->isEmpty()) {
            throw new ModelNotFoundException("Nessun cliente corrispondente alla chiave indicata nella società suite {$suite->id} (i clienti eliminati o anonimizzati non sono importabili).");
        }

        return $matches->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributesFrom(SuiteClient $row, Company $local): array
    {
        $name = $this->clean($row->name)
            ?? $this->clean(trim((string) $row->first_name.' '.(string) $row->last_name))
            ?? "Cliente suite {$row->id}";

        return [
            'company_id' => $local->id,
            'subject_type' => (bool) $row->is_company ? 'company' : 'person',
            'name' => $name,
            'first_name' => $this->clean($row->first_name),
            'last_name' => $this->clean($row->last_name),
            'tax_code' => $this->clean($row->tax_code, 16),
            'vat_number' => $this->clean($row->vat_number, 50),
            'email' => $this->clean($row->email),
            'pec' => $this->clean($row->pec),
            'phone' => $this->clean($row->phone, 50),
            'sdi_code' => $this->clean($row->sdi_code, 7),
            'address' => $this->clean($row->address),
            'city' => $this->clean($row->city),
            'zip_code' => $this->clean($row->zip_code, 20),
            'country' => $this->clean($row->country, 2, true),
        ];
    }

    private function clean(mixed $value, int $maxLength = 255, bool $uppercase = false): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = mb_substr(trim((string) $value), 0, $maxLength);

        return $uppercase ? mb_strtoupper($value) : $value;
    }

    private function assertImportable(Client $existing, Company $local): void
    {
        if ($existing->company_id !== $local->id) {
            throw new RuntimeException("Il cliente locale {$existing->id} collegato appartiene a un'altra società: non viene spostato.");
        }

        if ($existing->trashed()) {
            throw new RuntimeException("Il cliente locale {$existing->id} collegato è stato eliminato: non viene reimportato.");
        }

        if ($existing->isAnonymized()) {
            throw new RuntimeException("Il cliente locale {$existing->id} collegato è anonimizzato: non viene reimportato.");
        }
    }

    /**
     * Cliente locale senza origine della stessa societa' con lo stesso codice fiscale o partita IVA.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function findAdoptable(Company $local, array $attributes): ?Client
    {
        $taxCode = $attributes['tax_code'];
        $vatNumber = $attributes['vat_number'];

        if ($taxCode === null && $vatNumber === null) {
            return null;
        }

        return Client::query()
            ->where('company_id', $local->id)
            ->whereNull('source_system')
            ->whereNull('anonymized_at')
            ->where(function ($query) use ($taxCode, $vatNumber): void {
                if ($taxCode !== null) {
                    $query->orWhereRaw('LOWER(TRIM(tax_code)) = ?', [mb_strtolower($taxCode)]);
                }

                if ($vatNumber !== null) {
                    $query->orWhereRaw('LOWER(TRIM(vat_number)) = ?', [mb_strtolower($vatNumber)]);
                }
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function update(Client $client, array $attributes): Client
    {
        $client->fill($attributes);

        if ($client->isDirty()) {
            $client->save();
        }

        return $client;
    }
}
