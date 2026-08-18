<?php

declare(strict_types=1);

namespace CoolMS\Entity\ValueObject;

/**
 * What a runtime-type module knows about one entity alias.
 *
 * Both facts arrive together because answering them costs one lookup. Split
 * across two port methods they would cost two, and memoising the record inside
 * the contributor to avoid that would go stale the moment a type is saved
 * mid-request.
 *
 * @see \CoolMS\Entity\Contract\EntityTypeSchemaContributorInterface
 */
final readonly class EntityTypeSchemaContribution
{
    /**
     * @param string[]                  $inheritanceChain aliases root to leaf, INCLUDING this
     *                                                    one; empty when there is nothing to
     *                                                    inherit (root type, or no parent chain)
     * @param array<string, mixed>|null $prebuiltSchema   the schema cached on the type record;
     *                                                    null when the cache has not been built
     */
    public function __construct(
        public array $inheritanceChain = [],
        public ?array $prebuiltSchema = null,
    ) {
    }
}
