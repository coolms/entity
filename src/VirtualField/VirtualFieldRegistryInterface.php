<?php

declare(strict_types=1);

namespace CoolMS\Entity\VirtualField;

use CoolMS\Entity\Filter\VirtualFieldDescriptor;

/**
 * Phase X-2.5b -- aggregates virtual field descriptors per entity
 * alias from two sources:
 *
 *  - `#[VirtualField]` attributes on the target entity's methods
 *    (discovered via Reflection on the FQCN resolved from the
 *    alias).
 *  - `VirtualFieldProviderInterface` implementations contributed
 *    by other modules.
 *
 * Implementations MUST be deterministic -- repeated calls for the
 * same alias produce identical descriptor lists in identical
 * order. Attribute-derived descriptors precede provider
 * contributions.
 */
interface VirtualFieldRegistryInterface
{
    /**
     * @return list<VirtualFieldDescriptor>
     */
    public function getForEntity(string $entityAlias): array;

    public function findByName(string $entityAlias, string $name): ?VirtualFieldDescriptor;
}
