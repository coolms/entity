<?php

declare(strict_types=1);

namespace CoolMS\Entity\Traits;

use CoolMS\Core\Mapping\Column;

/**
 * Provides a `sortOrder` integer field for orderable entities.
 *
 * Usage:
 *   class NaviNode implements OrderableInterface
 *   {
 *       use OrderProviderTrait;
 *   }
 */
trait OrderProviderTrait
{
    #[Column(name: 'sort_order', type: 'integer', options: ['default' => 0])]
    public int $sortOrder = 0;

    public function reorder(int $position): static
    {
        $this->sortOrder = $position;

        return $this;
    }
}
