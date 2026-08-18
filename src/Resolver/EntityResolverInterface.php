<?php

declare(strict_types=1);

namespace CoolMS\Entity\Resolver;

/**
 * Resolves entity references for template context hydration and
 * picker UI search. One resolver per entity type, dispatched via
 * {@see EntityResolverChainInterface} in priority order — custom
 * resolvers can outrank the default reflection-based fallback.
 *
 * Implementations:
 *  - MUST be stateless (the chain is a shared service).
 *  - MUST return JSON-serializable arrays — the result lands inside
 *    the DocumentInstance's render context and travels through
 *    Messenger envelopes in some flows.
 *  - SHOULD return `null` from `resolve()` on a missing entity; the
 *    hydrating contributor turns that into the `__deleted` sentinel.
 *  - SHOULD apply permission filtering at the repository layer where
 *    relevant — the picker endpoint trusts the resolver to scope
 *    results to the requesting user.
 *
 * Concrete resolvers are collected by `EntityResolverChain` without
 * extra wiring: the consuming bundle calls
 * `registerForAutoconfiguration(EntityResolverInterface::class)` and
 * tags them `coolms.entity_resolver`. The tag is declared there rather
 * than by an attribute here so this package needs no dependency on the
 * container it happens to run in.
 */
interface EntityResolverInterface
{
    /**
     * Whether this resolver handles `$entityType`. Chain dispatch
     * picks the first `supports() === true`; priority is the tag
     * `priority` attribute (higher first).
     */
    public function supports(string $entityType): bool;

    /**
     * Resolve a single entity ID into a flattened context dict.
     * Returns `null` when the entity no longer exists.
     *
     * @param list<string>|null $fields Optional allow-list of fields to expose. Null = resolver default.
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $entityType, string|int $id, ?array $fields = null): ?array;

    /**
     * Picker-side search. Each result carries an id + display label;
     * optional `secondary` provides a second line (email, slug, etc.)
     * the picker UI can render.
     *
     * @return list<array{id: string|int, label: string, secondary?: string}>
     */
    public function search(string $entityType, string $query, int $limit = 20): array;
}
