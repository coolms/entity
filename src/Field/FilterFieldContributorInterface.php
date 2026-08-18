<?php

declare(strict_types=1);

namespace CoolMS\Entity\Field;

/**
 * Contributes filterable fields that are NOT derivable from the entity class
 * itself.
 *
 * `ReflectionEntityFieldDescriptor` can only see what `#[FieldMeta]` declares
 * on a property, which excludes two whole categories an operator legitimately
 * wants to filter on:
 *
 *   - **relation paths** — "is in group X" is `groups.id`, a traversal, not a
 *     column on `User`;
 *   - **columns owned by a shared trait** — `createdAt` lives in
 *     `TimestampableTrait`, so annotating it there would expose it on every
 *     entity in the platform whether or not that made sense.
 *
 * A grid-configuration module may already declare both, per grid, in YAML (`field:`
 * aliases plus `filterOp`), and `FilterAudienceMaterializer` already merges
 * those columns into the RQL allow-list. So the BACKEND accepted
 * `filter=groupId eq <uuid>` all along — the wizard simply never offered it,
 * because its field endpoint consulted the descriptor alone. This seam closes
 * that gap so "what the UI offers" and "what the query layer accepts" come
 * from the same place.
 *
 * Implementations are auto-tagged `coolms.entity.filter_field_contributor`.
 * Declared here and implemented by the consuming grid module, so the
 * dependency points upward — this package must not know about grids.
 */
interface FilterFieldContributorInterface
{
    /**
     * Extra filterable fields for `$alias`, or an empty list when this
     * contributor has nothing to add.
     *
     * A field whose name collides with one the entity itself declares is
     * DISCARDED by the provider: the entity's own annotation is the more
     * specific statement and wins.
     *
     * @param string       $alias entity alias as used in the URL (`user`, `navi_node`, …)
     * @param class-string $fqcn  resolved entity class
     *
     * @return list<FieldDescriptor>
     */
    public function contribute(string $alias, string $fqcn): array;
}
