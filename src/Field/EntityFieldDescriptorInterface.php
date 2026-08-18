<?php

declare(strict_types=1);

namespace CoolMS\Entity\Field;

/**
 * Phase X-2.5 — entity field-capability service.
 *
 * Single source of truth for "what can be filtered/sorted/searched
 * on this entity type." Reads `#[FieldMeta]` attributes from the
 * entity class via Reflection and emits a flat list of
 * `FieldDescriptor` value objects.
 *
 * Used by:
 *  - `EntityFieldsProvider` — exposes the list at
 *    `GET /api/v1/entity/{alias}/filters` so the document-generation
 *    wizard frontend can render filter UI per entity type.
 *  - `FilterAudienceMaterializer` — builds the RQL whitelist
 *    (allowedFields) for the audience-filter query.
 *
 * Implementations MUST be deterministic — repeated calls for the
 * same FQCN produce identical descriptor lists in identical order.
 */
interface EntityFieldDescriptorInterface
{
    /**
     * @param class-string $entityClass
     *
     * @return list<FieldDescriptor>
     */
    public function describe(string $entityClass): array;
}
