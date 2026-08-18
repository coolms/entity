<?php

declare(strict_types=1);

namespace CoolMS\Entity\Contract;

use CoolMS\Entity\ValueObject\EntityTypeSchemaContribution;

/**
 * OPTIONAL contributor that lets a module define entity TYPES at runtime and
 * have their schemas inherit down a parent chain.
 *
 * Entity's extras engine works without it: an install with no runtime-type
 * module still resolves every alias through the static-config + record merge.
 * The contributor only adds two things on top -- a parent chain to merge
 * along, and a pre-built schema cache that short-circuits that merge.
 *
 * This is the one genuinely optional port of the three. An absent contributor
 * means "no type inheritance", which is exactly right for an install without a
 * runtime-type module -- not a silently disabled feature. Every other module
 * that declares extras keeps working.
 *
 * Implemented by a runtime-types module, which requires this package.
 * Never the reverse.
 */
interface EntityTypeSchemaContributorInterface
{
    /**
     * What this module knows about $entityAlias, or null when the alias is not
     * one of its runtime types at all.
     */
    public function getSchemaContribution(string $entityAlias): ?EntityTypeSchemaContribution;
}
