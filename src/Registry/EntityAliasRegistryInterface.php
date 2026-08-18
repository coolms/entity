<?php

declare(strict_types=1);

namespace CoolMS\Entity\Registry;

use CoolMS\Core\Attribute\ClassMeta;

/**
 * Author-facing entity-alias dictionary.
 *
 * Templates declare entity references via the `@alias` prefix
 * (`{var:@user.email}`); the `DtmplSyntaxValidator`
 * consults this registry at schema-build time, stamps the resolved
 * FQCN onto the matching `ContextSchemaVariable`, and the picker
 * UX + render-time hydrator inherit that marker without any
 * post-upload CLI step.
 *
 * Implementations:
 *  - MUST be stateless / shared (consulted once per template
 *    upload).
 *  - SHOULD return identical FQCNs for the same alias on every
 *    call so retries against the same template produce stable
 *    schemas.
 *  - MAY be composed (e.g. the static `#[ClassMeta(alias:…)]`
 *    source layered on top of the EntityAliasRegistry).
 */
interface EntityAliasRegistryInterface
{
    /**
     * Resolve an alias to its fully-qualified entity class name.
     * Accepts both the singular `alias` and the `aliasCollection`
     * form; returns the same FQCN either way. Returns `null` when
     * the alias is unknown — callers turn that into a clear
     * user-facing error rather than guessing.
     *
     * @return class-string|null
     */
    public function resolve(string $alias): ?string;

    /**
     * Reverse lookup — the singular alias declared for an FQCN, or
     * `null` when no alias is registered. The collection-form alias
     * is not returned here; use `findByAlias` then read the meta to
     * discover both forms.
     */
    public function aliasOf(string $entityType): ?string;

    /**
     * True when `$alias` is registered as either a singular alias
     * or a collection alias on some declared ClassMeta.
     */
    public function has(string $alias): bool;

    /**
     * The full singular-alias → FQCN map. Useful for error messages
     * ("Available aliases: …") and admin tooling. Collection
     * aliases are not included here; query them via `findByAlias`
     * or `isCollectionAlias`.
     *
     * @return array<string, class-string>
     */
    public function all(): array;

    /**
     * Return the ClassMeta whose `alias` or `aliasCollection`
     * equals `$alias`, or `null` when no such meta is registered.
     */
    public function findByAlias(string $alias): ?ClassMeta;

    /**
     * True when `$alias` matches some declared `aliasCollection`.
     * False when it matches only a singular alias and false when
     * the alias is not registered at all.
     */
    public function isCollectionAlias(string $alias): bool;
}
