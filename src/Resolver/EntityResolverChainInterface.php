<?php

declare(strict_types=1);

namespace CoolMS\Entity\Resolver;

/**
 * Dispatches entity-resolution calls to the first registered
 * {@see EntityResolverInterface} whose `supports()` returns true.
 * Consumed by `EntityHydratingContributor` at render time and by
 * `EntitySearchController` at picker-time.
 */
interface EntityResolverChainInterface
{
    /**
     * @param list<string>|null $fields
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $entityType, string|int $id, ?array $fields = null): ?array;

    /**
     * @return list<array{id: string|int, label: string, secondary?: string}>
     */
    public function search(string $entityType, string $query, int $limit = 20): array;
}
