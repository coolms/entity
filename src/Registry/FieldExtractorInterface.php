<?php

declare(strict_types=1);

namespace CoolMS\Entity\Registry;

/**
 * Flattens entity objects to JSON-serializable arrays for inclusion
 * in template render context. The default reflection-based
 * implementation lives in `Core\Infrastructure\Entity\ReflectionFieldExtractor`;
 * module-specific custom extractors can replace it via a custom
 * `EntityResolverInterface` registered at higher priority.
 *
 * `extract()` is responsible for the heavy lifting (full record),
 * `extractLabel()` is the picker-friendly one-liner.
 */
interface FieldExtractorInterface
{
    /**
     * Flatten entity to a JSON-serializable dict.
     *
     * @param list<string>|null $fields allow-list of field names; null exposes the extractor's defaults
     *
     * @return array<string, mixed>
     */
    public function extract(object $entity, ?array $fields = null): array;

    /**
     * One-line display label for the picker dropdown.
     */
    public function extractLabel(object $entity): string;

    /**
     * Optional secondary line for the picker (e.g. email, slug).
     * Implementations MAY return null when nothing meaningful is available.
     */
    public function extractSecondary(object $entity): ?string;

    /**
     * Entity-id extraction. Centralised here so resolvers stay
     * id-shape-agnostic (Uuid vs int vs string).
     */
    public function extractId(object $entity): string|int;
}
