<?php

declare(strict_types=1);

namespace CoolMS\Entity;

interface DescriptionProviderInterface
{
    /**
     * Human-readable display description.
     */
    public string $description {
        get;
        set;
    }
}
