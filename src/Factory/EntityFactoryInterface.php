<?php

declare(strict_types=1);

namespace CoolMS\Entity\Factory;

use CoolMS\Core\Identifier\IdentifierProviderInterface;
use CoolMS\Core\Service\DataFormat;
use CoolMS\Core\Service\ProcessorInterface;
use CoolMS\Core\Service\ProviderInterface;

/**
 * Entity Factory Interface.
 *
 * Combines hydration (array -- object) and serialization (object -- array/JSON)
 * contracts with an explicit create() and update() API.
 */
interface EntityFactoryInterface extends ProcessorInterface, ProviderInterface
{
    /**
     * Hydrate a new entity from an array, JSON, or XML (no persistence).
     * Sets timestamp fields (createdAt / updatedAt / accessedAt) when applicable.
     *
     * @param array<string, mixed>|string $data
     * @param array<string, mixed>        $context
     */
    public function create(array|string $data, ?DataFormat $format = null, array $context = []): IdentifierProviderInterface;

    /**
     * Patch an existing entity from an array, JSON, or XML (no persistence).
     * Updates updatedAt when applicable.
     *
     * @param array<string, mixed>|string $data
     * @param array<string, mixed>        $context
     */
    public function update(array|string $data, ?DataFormat $format = null, array $context = []): IdentifierProviderInterface;
}
