<?php

namespace App\Models\Suite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use LogicException;

/**
 * Base dei modelli del database `unicosuite`, condiviso e di sola lettura:
 * ogni scrittura viene rifiutata a livello applicativo.
 */
abstract class SuiteModel extends Model
{
    protected $connection = 'unicosuite';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        throw $this->readOnlyException();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $options
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw $this->readOnlyException();
    }

    public function delete(): ?bool
    {
        throw $this->readOnlyException();
    }

    public function forceDelete(): ?bool
    {
        throw $this->readOnlyException();
    }

    protected function newBaseQueryBuilder(): Builder
    {
        $connection = $this->getConnection();

        return new ReadOnlyQueryBuilder(
            $connection,
            $connection->getQueryGrammar(),
            $connection->getPostProcessor(),
        );
    }

    protected function readOnlyException(): LogicException
    {
        return new LogicException(static::class.' è di sola lettura: il database unicosuite non si modifica.');
    }
}
