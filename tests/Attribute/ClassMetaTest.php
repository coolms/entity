<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Attribute;

use CoolMS\Core\Attribute\ClassMeta;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the ClassMeta attribute value object.
 */
final class ClassMetaTest extends TestCase
{
    public function testClassMetaCarriesAliasCollection(): void
    {
        $meta = new ClassMeta(label: 'Invoice', alias: 'invoice', aliasCollection: 'invoices');

        self::assertSame('Invoice', $meta->label);
        self::assertSame('invoice', $meta->alias);
        self::assertSame('invoices', $meta->aliasCollection);
    }

    public function testClassMetaAliasCollectionOptional(): void
    {
        $meta = new ClassMeta(label: 'Invoice', alias: 'invoice');

        self::assertSame('invoice', $meta->alias);
        self::assertNull($meta->aliasCollection);
    }

    public function testClassMetaAliasOptionalToo(): void
    {
        $meta = new ClassMeta(label: 'Bare');

        self::assertNull($meta->alias);
        self::assertNull($meta->aliasCollection);
    }
}
