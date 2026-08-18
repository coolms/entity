<?php

declare(strict_types=1);

namespace CoolMS\Entity\Traits;

use CoolMS\Core\Mapping\Column;
use Symfony\Component\Serializer\Attribute\Groups;

trait LabelProviderTrait
{
    public function __construct(
        #[Column(type: 'string')]
        #[Groups(['read', 'list', 'search', 'stat', 'write'])]
        public string $label,
    ) {
    }
}
