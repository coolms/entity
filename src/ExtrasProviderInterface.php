<?php

declare(strict_types=1);

namespace CoolMS\Entity;

interface ExtrasProviderInterface
{
    /** @var array<string, mixed> */
    public array $extras { get; set; }
}
