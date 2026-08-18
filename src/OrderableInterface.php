<?php

declare(strict_types=1);

namespace CoolMS\Entity;

/**
 * Marks an entity as orderable -- it has a `sortOrder` position
 * that can be used for drag-and-drop reordering in lists.
 *
 * Implemented via OrderProviderTrait.
 */
interface OrderableInterface
{
    public int $sortOrder { get; set; }

    /**
     * Move this entity to the given position.
     * Does not persist -- caller is responsible for saving.
     */
    public function reorder(int $position): static;
}
