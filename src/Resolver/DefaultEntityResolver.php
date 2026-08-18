<?php

declare(strict_types=1);

namespace CoolMS\Entity\Resolver;

use CoolMS\Entity\Registry\FieldExtractorInterface;
use CoolMS\Entity\Registry\RepositoryRegistryInterface;
use CoolMS\Entity\Search\FreeTextProjector;
use CoolMS\Rql\RqlContext;
use CoolMS\Rql\RqlQuery;
use CoolMS\Rql\RqlRepositoryInterface;
use RuntimeException;

/**
 * Reflection-based fallback resolver. Looked up via
 * {@see RepositoryRegistryInterface} to find each entity type's
 * repository, then delegates field extraction to
 * {@see FieldExtractorInterface}.
 *
 * Priority is 0, the tag default — custom resolvers register at
 * higher priorities to take over before the fallback fires. Tagged by
 * autoconfiguration on `EntityResolverInterface`, declared by the
 * consuming bundle rather than by an attribute here.
 *
 * `supports()` returns true for any entity type that has a
 * registered repository — the actual class-string sniff happens
 * inside the registry.
 */
final readonly class DefaultEntityResolver implements EntityResolverInterface
{
    public function __construct(
        private RepositoryRegistryInterface $repositories,
        private FieldExtractorInterface $extractor,
        private FreeTextProjector $projector,
    ) {
    }

    public function supports(string $entityType): bool
    {
        return $this->repositories->has($entityType);
    }

    public function resolve(string $entityType, string|int $id, ?array $fields = null): ?array
    {
        $repo = $this->repositories->get($entityType);
        $entity = $this->findById($repo, $id);
        if (null === $entity) {
            return null;
        }

        return $this->extractor->extract($entity, $fields);
    }

    public function search(string $entityType, string $query, int $limit = 20): array
    {
        $repo = $this->repositories->get($entityType);
        /** @var class-string $entityType */
        $projected = $this->projector->project($entityType, $query);
        $filters = null === $projected ? [] : [$projected];
        $rql = new RqlQuery(filters: $filters, limit: $limit);
        $result = $repo->findByRql($rql, new RqlContext('e'));
        $rows = [];
        foreach ($result->items as $entity) {
            $row = [
                'id' => $this->extractor->extractId($entity),
                'label' => $this->extractor->extractLabel($entity),
            ];
            $secondary = $this->extractor->extractSecondary($entity);
            if (null !== $secondary) {
                $row['secondary'] = $secondary;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Repositories vary in their by-id method name -- `findById` is
     * the canonical signature on every interface we care about
     * today, but a couple of legacy ones expose only the ORM-style
     * `find(mixed $id)`. Probe both.
     */
    private function findById(RqlRepositoryInterface $repo, string|int $id): ?object
    {
        if (method_exists($repo, 'findById')) {
            /** @var object|null $entity */
            $entity = $repo->findById($id);

            return $entity;
        }
        if (method_exists($repo, 'find')) {
            /** @var object|null $entity */
            $entity = $repo->find($id);

            return $entity;
        }

        throw new RuntimeException(sprintf('Repository %s lacks both `findById()` and `find()`; the default resolver cannot fetch by id.', $repo::class));
    }
}
