<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use ArrayAccess;
use CoolMS\Entity\ExtrasProviderInterface;
use CoolMS\Entity\Traits\ExtrasProviderTrait;

/**
 * An entity with an extras bag, in the shape a consumer writes one.
 *
 * ArrayAccess is declared by the entity, not by the trait: the trait supplies
 * the offset methods, and the entity opts in so that `[field]` property paths
 * (Symfony PropertyAccess, dynamic forms) reach the bag.
 *
 * `$title` is a declared property, so it never goes through the bag. `$bio`
 * is not declared: it is an extras field, and the `@property` tag only tells
 * static analysis that code here names it directly.
 *
 * @implements ArrayAccess<string, mixed>
 *
 * @property mixed $bio
 */
final class ExtrasEntity implements ExtrasProviderInterface, ArrayAccess
{
    use ExtrasProviderTrait;

    public string $title = '';
}
