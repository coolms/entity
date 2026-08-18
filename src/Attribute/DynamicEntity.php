<?php

declare(strict_types=1);

namespace CoolMS\Entity\Attribute;

use Attribute;

/**
 * Marks a class as a DynamicEntity and assigns its alias.
 *
 * Consumed by each module's Extension::load() to register the class
 * with EntityAliasRegistry via addMethodCall('register', ...).
 *
 * Usage:
 *   #[DynamicEntity(alias: 'page_variant')]
 *   class PageVariant extends AbstractDynamicEntity { ... }
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DynamicEntity
{
    public function __construct(
        public string $alias,
    ) {
    }
}
