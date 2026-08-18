<?php

declare(strict_types=1);

namespace CoolMS\Entity\Resolver;

/**
 * Default chain implementation. Iterates registered resolvers in
 * priority order (highest tag `priority` first) and dispatches to the
 * first `supports() === true`. Returns `null` from `resolve()` /
 * empty list from `search()` when no resolver matches —
 * `EntityHydratingContributor` turns `null` into the deleted
 * sentinel, the search endpoint surfaces empty as a clean 200.
 *
 * The iterator is supplied by the consuming bundle's extension, not by
 * an attribute here, so this package does not depend on the container.
 */
final readonly class EntityResolverChain implements EntityResolverChainInterface
{
    /**
     * @param iterable<EntityResolverInterface> $resolvers ordered highest tag `priority` first
     */
    public function __construct(
        private iterable $resolvers = [],
    ) {
    }

    public function resolve(string $entityType, string|int $id, ?array $fields = null): ?array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($entityType)) {
                return $resolver->resolve($entityType, $id, $fields);
            }
        }

        return null;
    }

    public function search(string $entityType, string $query, int $limit = 20): array
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($entityType)) {
                return $resolver->search($entityType, $query, $limit);
            }
        }

        return [];
    }
}
