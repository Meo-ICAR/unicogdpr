<?php

namespace App\Models\Suite;

use Illuminate\Database\Query\Builder;
use LogicException;

/**
 * Query builder del database `unicosuite`: legge soltanto, ogni scrittura
 * viene rifiutata.
 */
class ReadOnlyQueryBuilder extends Builder
{
    /**
     * @param  array<int|string, mixed>  $values
     */
    public function insert(array $values): bool
    {
        throw $this->readOnlyException();
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    public function insertGetId(array $values, $sequence = null): int
    {
        throw $this->readOnlyException();
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    public function insertOrIgnore(array $values): int
    {
        throw $this->readOnlyException();
    }

    public function insertUsing(array $columns, $query): int
    {
        throw $this->readOnlyException();
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function update(array $values): int
    {
        throw $this->readOnlyException();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    public function updateOrInsert(array $attributes, $values = []): bool
    {
        throw $this->readOnlyException();
    }

    public function upsert(array $values, $uniqueBy, $update = null): int
    {
        throw $this->readOnlyException();
    }

    public function delete($id = null): int
    {
        throw $this->readOnlyException();
    }

    public function truncate(): void
    {
        throw $this->readOnlyException();
    }

    public function increment($column, $amount = 1, array $extra = []): int
    {
        throw $this->readOnlyException();
    }

    public function decrement($column, $amount = 1, array $extra = []): int
    {
        throw $this->readOnlyException();
    }

    protected function readOnlyException(): LogicException
    {
        return new LogicException('Il database unicosuite è di sola lettura.');
    }
}
