<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Fixture;

use CoolMS\Entity\LabelProviderInterface;
use CoolMS\Entity\Traits\LabelProviderTrait;

/**
 * An entity with a label, in the shape a consumer writes one when it has a
 * constructor of its own.
 *
 * The trait's constructor is aliased and called from the entity's constructor.
 * That is how one class combines several traits that each declare a
 * constructor.
 */
final class LabelledEntity implements LabelProviderInterface
{
    use LabelProviderTrait {
        LabelProviderTrait::__construct as private initLabel;
    }

    public function __construct(string $label = '', public string $slug = '')
    {
        $this->initLabel($label);
    }
}
