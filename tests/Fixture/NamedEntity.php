<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use CoolMS\Entity\NameProviderInterface;
use CoolMS\Entity\Traits\NameProviderTrait;

/**
 * An entity with a name, in the simplest shape a consumer writes one: the
 * trait's constructor is the entity's constructor.
 */
final class NamedEntity implements NameProviderInterface
{
    use NameProviderTrait;
}
