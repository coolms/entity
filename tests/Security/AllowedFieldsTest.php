<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Security;

use CoolMS\Entity\Security\AllowedFields;
use CoolMS\Entity\Security\NoRecordIsReadable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AllowedFieldsTest extends TestCase
{
    #[Test]
    public function aReadAskingForNoFieldInParticularGetsEveryAllowedOne(): void
    {
        self::assertSame(['id', 'name'], AllowedFields::narrow(null, ['id', 'name']));
    }

    #[Test]
    public function aReadGetsWhatItAskedForThatIsAllowedInTheOrderAskedOnce(): void
    {
        self::assertSame(['name', 'id'], AllowedFields::narrow(['secret', 'name', 'id', 'name'], ['id', 'name']));
        self::assertSame([], AllowedFields::narrow(['secret'], ['id', 'name']));
        self::assertSame([], AllowedFields::narrow(['id'], []));
    }

    #[Test]
    public function theDefaultGuardRefusesEveryRecordAndEveryPredicate(): void
    {
        $guard = new NoRecordIsReadable();

        self::assertNull($guard->fieldsFor(new stdClass()));
        self::assertSame([], $guard->predicateFieldsFor(stdClass::class));
    }
}
