<?php

declare(strict_types=1);

namespace CoolMS\Entity\Resolver;

/**
 * Adapter contract for resolving an entity alias and an optional
 * RQL filter to entity objects.
 *
 * Distinct from `CoolMS\Entity\Resolver\EntityResolverInterface`,
 * which addresses entities by FQCN and returns flattened arrays for
 * picker / hydration flows. This interface addresses entities by
 * author-facing alias and returns raw domain objects for template
 * widgets (`entity:find` / `entity:findAll`).
 *
 * Implementations may fetch from a local repository, a remote API,
 * or any other source. The widget layer depends only on this
 * interface, not on any specific fetch mechanism.
 */
interface EntityAliasResolverInterface
{
    /**
     * Resolve an alias and optional RQL filter to at most one
     * entity. Returns `null` when no entity matches the filter or
     * the alias has no registered class.
     */
    public function find(string $alias, ?string $rqlFilter = null): ?object;

    /**
     * Resolve an alias and optional RQL filter to a list of
     * entities. Returns an empty array when no entities match or
     * the alias has no registered class.
     *
     * @return array<int, object>
     */
    public function findAll(string $alias, ?string $rqlFilter = null): array;
}
