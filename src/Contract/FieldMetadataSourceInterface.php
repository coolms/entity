<?php

declare(strict_types=1);

namespace CoolMS\Entity\Contract;

/**
 * Supplies resolved per-property field metadata and override provenance to the
 * entity-introspection provider ({@see \CoolMS\Entity\Repository\EntitySchemaProviderInterface}).
 *
 * Separate from {@see FieldSchemaSourceInterface} on purpose: that port answers
 * "what extras fields does this alias have", this one answers "what does the
 * field-management module know about this class's properties, and where did
 * the knowledge come from". Introspection consumers need the second and not
 * the first.
 *
 * Both are implemented by the same adapter today; keeping them apart means
 * neither consumer depends on methods it does not call.
 */
interface FieldMetadataSourceInterface
{
    /**
     * Record-defined fields for an alias, keyed by field name.
     *
     * Only the two attributes introspection needs -- the full config lives
     * behind {@see FieldSchemaSourceInterface::getRuntimeFields()}.
     *
     * @return array<string, array{type: string, label: string}>
     */
    public function getRuntimeFieldSummaries(string $entityAlias): array;

    /**
     * Merged metadata for every property the field-management module knows
     * about on $className, keyed by property name.
     *
     * `private` marks an explicit opt-out; `hasMeta` says whether a PHP
     * attribute declared the property (as opposed to it being synthesised from
     * a config file or a record), which is what lets a caller tell a computed
     * property from a declared one.
     *
     * @param class-string $className
     *
     * @return array<string, array{private: bool, hasMeta: bool, label: string, formType: string|null, sortOrder: int|null}>
     */
    public function getResolvedFieldMetadata(string $className, string $entityAlias): array;

    /**
     * True when $fieldName carries a config-file override for $entityAlias --
     * i.e. a file, not a PHP attribute and not a stored record.
     */
    public function hasFileOverride(string $entityAlias, string $fieldName): bool;
}
