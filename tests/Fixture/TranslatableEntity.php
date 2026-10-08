<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use CoolMS\Entity\Traits\ExtrasProviderTrait;
use CoolMS\Entity\Traits\TranslatableProviderTrait;
use CoolMS\Entity\TranslatableProviderInterface;

/**
 * An entity with translatable fields, in the shape a consumer writes one.
 *
 * The translation trait stores its values in the extras bag, so it is always
 * used together with the extras trait.
 */
final class TranslatableEntity implements TranslatableProviderInterface
{
    use ExtrasProviderTrait;
    use TranslatableProviderTrait;
}
