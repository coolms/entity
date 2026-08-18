<?php

declare(strict_types=1);

namespace CoolMS\Entity\Registry;

use CoolMS\Rql\RqlRepositoryInterface;
use OutOfBoundsException;

/**
 * Maps entity FQCN -- {@see RqlRepositoryInterface}. Used by
 * `DefaultEntityResolver` and `RepositoryEntityAliasResolver` to
 * discover which repository owns a given entity type. Modules opt
 * in by tagging their repository `coolms.entity_repository` with
 * an `entityType` attribute carrying the FQCN.
 *
 * Lookup is by string FQCN -- keeps the resolver chain free of
 * concrete entity-class imports and stays ORM-agnostic.
 */
interface RepositoryRegistryInterface
{
    public function has(string $entityType): bool;

    /**
     * @throws OutOfBoundsException when no repository is registered for `$entityType`
     */
    public function get(string $entityType): RqlRepositoryInterface;
}
