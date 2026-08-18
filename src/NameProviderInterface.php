<?php

declare(strict_types=1);

namespace CoolMS\Entity;

interface NameProviderInterface
{
    /**
     * Technical identifier name -- stable, not translatable.
     */
    public string $name {
        get;
        set;
    }
}
