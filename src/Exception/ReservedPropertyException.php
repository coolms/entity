<?php

declare(strict_types=1);

namespace CoolMS\Entity\Exception;

use LogicException;

final class ReservedPropertyException extends LogicException
{
    public function __construct(string $propertyName, string $entityClass)
    {
        parent::__construct(sprintf(
            'Property "%s" is reserved and cannot be overwritten dynamically in "%s". '
            . 'It maps to the extras bag itself.',
            $propertyName,
            $entityClass,
        ));
    }
}
