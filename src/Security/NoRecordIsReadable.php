<?php

declare(strict_types=1);

namespace CoolMS\Entity\Security;

/**
 * The guard a host gets when it declares none: no record is readable and no predicate is allowed. A template then
 * renders every record as unavailable, never as fully readable, until the host says what may be read.
 */
final readonly class NoRecordIsReadable implements RecordReadGuardInterface
{
    public function fieldsFor(object $record): ?array
    {
        return null;
    }

    public function predicateFieldsFor(string $class): array
    {
        return [];
    }
}
