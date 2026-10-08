<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use CoolMS\Entity\TaggableExtrasProviderInterface;
use CoolMS\Entity\Traits\ExtrasProviderTrait;
use CoolMS\Entity\Traits\TaggableExtrasProviderTrait;

/**
 * A taggable entity, in the shape a consumer writes one.
 *
 * The tagging trait stores its tags in the extras bag, so it is always used
 * together with the extras trait.
 */
final class TaggableEntity implements TaggableExtrasProviderInterface
{
    use ExtrasProviderTrait;
    use TaggableExtrasProviderTrait;
}
