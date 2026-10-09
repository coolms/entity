<?php

declare(strict_types=1);

namespace CoolMS\Entity\Security;

use function in_array;

/**
 * The fields a read returns: the ones it asked for that the guard allows, in the order asked; every allowed field when
 * it asked for none in particular. Never a field the guard did not allow.
 */
final class AllowedFields
{
    /**
     * @param list<string>|null $requested
     * @param list<string>      $allowed
     *
     * @return list<string>
     */
    public static function narrow(?array $requested, array $allowed): array
    {
        if (null === $requested) {
            return $allowed;
        }
        $fields = [];
        foreach ($requested as $field) {
            if (in_array($field, $allowed, true) && !in_array($field, $fields, true)) {
                $fields[] = $field;
            }
        }

        return $fields;
    }
}
