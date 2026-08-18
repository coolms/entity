<?php

declare(strict_types=1);

namespace CoolMS\Entity\Contract;

/**
 * Lets a module claim a type for its own normalizer so the generic
 * extras-flattening normalizer stands aside.
 *
 * The claim is a safety net, not the primary mechanism: a claiming module
 * registers its normalizer at a higher priority, which already wins. This port
 * exists so that when the higher-priority normalizer is ABSENT -- the module
 * is not installed, or the serializer is built by hand in a test -- the
 * flattening normalizer does not quietly take over a type it would mishandle.
 *
 * Services implementing this are collected by the `coolms.extras_normalization_exclusion`
 * tag (see the Entity extension's registerForAutoconfiguration).
 */
interface ExtrasNormalizationExclusionInterface
{
    /**
     * True when the given object -- or class-string, on the denormalize side
     * where no instance exists yet -- is normalized elsewhere.
     *
     * The string form is deliberately NOT narrowed to `class-string`: API
     * Platform passes resource types that may not resolve to a loadable class,
     * and an implementation must answer false for those rather than blow up
     * (`is_a($x, ..., true)` does).
     */
    public function excludes(object|string $objectOrClass): bool;
}
