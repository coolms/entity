<?php

declare(strict_types=1);

namespace CoolMS\Entity\Security;

/**
 * Whether a template may read a record, and which of its fields.
 *
 * Every path that hands a record to a template asks it: the reference resolvers and the alias widgets. It is asked
 * after the record is loaded and before anything of it is projected, so a refused record never reaches a projection.
 * The answer is the caller's: the application's guard knows who is calling from wherever its security keeps that.
 *
 * A host that wires none gets {@see NoRecordIsReadable}, which refuses every record.
 */
interface RecordReadGuardInterface
{
    /**
     * The fields a template may read of `$record`, or null when it may read none of it. A refused record renders exactly
     * like a missing one, so a template cannot tell whether a record it may not read exists.
     *
     * @return list<string>|null
     */
    public function fieldsFor(object $record): ?array;

    /**
     * The fields a template's filter or sort may name for records of `$class`. A level that grants the caller every
     * record of the class allows its own fields; otherwise only the fields every level of the class grants. An empty list
     * allows no predicate at all.
     *
     * @param class-string $class
     *
     * @return list<string>
     */
    public function predicateFieldsFor(string $class): array;
}
