<?php

declare(strict_types=1);

namespace CoolMS\Entity\Factory;

use InvalidArgumentException;

/**
 * Entity Factory Factory (Abstract Factory).
 *
 * Acts as a runtime registry: given an entity interface class name, returns the
 * corresponding EntityFactoryInterface. Modules register their factories in DI
 * configuration.
 */
interface EntityFactoryFactoryInterface
{
    /**
     * Return the EntityFactory registered for $entityClass.
     *
     * @throws InvalidArgumentException if no factory is registered for $entityClass
     */
    public function get(string $entityClass): EntityFactoryInterface;

    /**
     * Return true if a factory is registered for $entityClass.
     */
    public function has(string $entityClass): bool;
}
