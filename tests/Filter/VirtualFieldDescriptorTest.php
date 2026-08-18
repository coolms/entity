<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Filter;

use Closure;
use CoolMS\Entity\Filter\VirtualFieldDescriptor;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Phase X-2.5b -- ORM-agnostic descriptor for a computed entity field.
 */
final class VirtualFieldDescriptorTest extends TestCase
{
    public function testSqlExpressionStrategyConstructs(): void
    {
        $descriptor = new VirtualFieldDescriptor(
            name: 'daysSinceLastLogin',
            label: 'Days since last login',
            filterType: 'int',
            sqlExpression: 'DATE_DIFF(CURRENT_DATE(), u.lastLoginAt)',
            allowedOps: ['eq', 'gt', 'lt'],
        );

        self::assertTrue($descriptor->hasSqlExpression());
        self::assertFalse($descriptor->hasTranslator());
        self::assertSame('daysSinceLastLogin', $descriptor->name);
        self::assertSame(['eq', 'gt', 'lt'], $descriptor->allowedOps);
    }

    public function testTranslatorStrategyConstructs(): void
    {
        $translator = static function (): void {};
        $descriptor = new VirtualFieldDescriptor(
            name: 'fullName',
            label: 'Full name',
            filterType: 'string',
            translator: $translator,
            allowedOps: ['eq', 'cn'],
        );

        self::assertFalse($descriptor->hasSqlExpression());
        self::assertTrue($descriptor->hasTranslator());
        self::assertInstanceOf(Closure::class, $descriptor->translator);
    }

    public function testNeitherStrategyRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must declare exactly one of sqlExpression or translator (got neither)');

        new VirtualFieldDescriptor(
            name: 'broken',
            label: 'Broken',
            filterType: 'string',
        );
    }

    public function testBothStrategiesRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must declare exactly one of sqlExpression or translator (got both)');

        new VirtualFieldDescriptor(
            name: 'broken',
            label: 'Broken',
            filterType: 'string',
            sqlExpression: 'COUNT(*)',
            translator: static function (): void {},
        );
    }

    public function testEmptyStringSqlExpressionTreatedAsAbsent(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('got neither');

        new VirtualFieldDescriptor(
            name: 'broken',
            label: 'Broken',
            filterType: 'string',
            sqlExpression: '',
        );
    }
}
