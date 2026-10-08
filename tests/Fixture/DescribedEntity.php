<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use CoolMS\Entity\DescriptionProviderInterface;
use CoolMS\Entity\Traits\DescriptionProviderTrait;

/**
 * An entity with a description, in the simplest shape a consumer writes one:
 * the trait's constructor is the entity's constructor.
 */
final class DescribedEntity implements DescriptionProviderInterface
{
    use DescriptionProviderTrait;
}
