<?php

declare(strict_types=1);

namespace CoolMS\Entity\Contract;

/**
 * Supplies the runtime (record-defined) half of an entity's extras schema.
 *
 * Entity owns the extras engine -- every module that declares
 * {@see \CoolMS\Entity\ExtrasProviderInterface} relies on it -- but it must
 * not know HOW field definitions are stored. That belongs to whichever module
 * manages them (Field today). This port is the seam.
 *
 * The port deliberately returns the NEUTRAL schema shape rather than definition
 * objects, so Entity never names the provider's classes. That is the whole
 * point: a port typed against `Definition[]` would be a wrapper, not a seam.
 *
 * Neutral shape -- fieldName => config map. Keys Entity itself reads:
 *
 *   source      string                     'runtime' for record-defined fields
 *   locked      bool                       cannot be deleted through the API
 *   security    array{read: string[], write: string[]}
 *   appliesTo   array<string, mixed>|null  per-instance applicability filter
 *
 * Everything else (type, label, isRequired, options, formConfig, apiConfig,
 * serializerConfig, sortOrder, group, widget, ...) is passed through untouched
 * to schema consumers.
 */
interface FieldSchemaSourceInterface
{
    /**
     * Runtime field configs for one entity alias, keyed by field name.
     *
     * Returns an empty array when the alias has no record-defined fields --
     * never null, so callers can merge unconditionally.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getRuntimeFields(string $entityAlias): array;
}
