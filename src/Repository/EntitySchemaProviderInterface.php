<?php

declare(strict_types=1);

namespace CoolMS\Entity\Repository;

use CoolMS\Entity\ValueObject\EntityClassMetadata;

interface EntitySchemaProviderInterface
{
    /**
     * Returns metadata for all known entity classes, including embeddables.
     * Sorted by module then short name -- grouping is left to the API layer.
     *
     * @return EntityClassMetadata[]
     */
    public function getAllEntities(): array;
}
