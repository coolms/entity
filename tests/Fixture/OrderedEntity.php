<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use CoolMS\Entity\OrderableInterface;
use CoolMS\Entity\Traits\OrderProviderTrait;

/**
 * An orderable entity, in the shape a consumer writes one.
 */
final class OrderedEntity implements OrderableInterface
{
    use OrderProviderTrait;
}
