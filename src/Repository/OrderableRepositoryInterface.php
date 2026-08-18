<?php

declare(strict_types=1);

namespace CoolMS\Entity\Repository;

/**
 * Repository contract for batch sortOrder updates.
 *
 * Implemented by repositories whose entity implements OrderableInterface.
 */
interface OrderableRepositoryInterface
{
    /**
     * Update sortOrder for multiple entities in a single query.
     *
     * @param array<string, int> $idToPosition Map of entity ID (RFC4122) to new sortOrder
     *
     * Example:
     *   $repo->reorderBatch([
     *     'uuid-1' => 10,
     *     'uuid-2' => 20,
     *     'uuid-3' => 30,
     *   ]);
     */
    public function reorderBatch(array $idToPosition): void;
}
