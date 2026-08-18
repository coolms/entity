<?php

declare(strict_types=1);

namespace CoolMS\Entity\Search;

use CoolMS\Entity\Field\EntityFieldDescriptorInterface;
use CoolMS\Rql\FilterNode;
use CoolMS\Rql\FilterOp;
use CoolMS\Rql\OrNode;

/**
 * Build an OrNode that projects a free-text query across an
 * entity's searchable fields. Returns null when the entity has no
 * searchable fields declared or the query string is empty.
 *
 * Replaces the legacy "hardcoded LIKE OR across N fields"
 * projection scattered across every SearchableRepositoryInterface
 * implementation. Per-entity searchable fields are declared via
 * `#[FieldMeta(searchable: true)]` and surfaced through
 * `EntityFieldDescriptorInterface`.
 */
final readonly class FreeTextProjector
{
    public function __construct(
        private EntityFieldDescriptorInterface $fields,
    ) {
    }

    /**
     * @param class-string $entityFqcn
     */
    public function project(string $entityFqcn, string $query): ?OrNode
    {
        if ('' === $query) {
            return null;
        }
        $searchable = $this->collectSearchableFields($entityFqcn);
        if ([] === $searchable) {
            return null;
        }
        $nodes = [];
        foreach ($searchable as $fieldName) {
            $nodes[] = new FilterNode($fieldName, FilterOp::Cn, $query);
        }

        return new OrNode($nodes);
    }

    /**
     * @param class-string $entityFqcn
     *
     * @return list<string>
     */
    private function collectSearchableFields(string $entityFqcn): array
    {
        $out = [];
        foreach ($this->fields->describe($entityFqcn) as $descriptor) {
            if ($descriptor->searchable) {
                $out[] = $descriptor->field;
            }
        }

        return $out;
    }
}
