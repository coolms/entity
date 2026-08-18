<?php

declare(strict_types=1);

namespace CoolMS\Entity;

interface LabelProviderInterface
{
    /**
     * Human-readable display label -- translatable, may change.
     */
    public string $label {
        get;
        set;
    }
}
