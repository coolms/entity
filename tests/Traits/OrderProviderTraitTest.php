<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Core\Mapping\Column;
use CoolMS\Entity\Tests\Fixture\OrderedEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * OrderProviderTrait gives an orderable entity its `sortOrder` position and
 * the reorder() that moves it, for drag-and-drop ordering in lists.
 *
 * reorder() only changes the position; saving it is the caller's job. The
 * fixture is the shape a consumer writes.
 */
final class OrderProviderTraitTest extends TestCase
{
    #[Test]
    public function aNewEntityIsAtPositionZero(): void
    {
        self::assertSame(0, new OrderedEntity()->sortOrder);
    }

    #[Test]
    public function reorderMovesTheEntityAndReturnsTheSameInstance(): void
    {
        $entity = new OrderedEntity();

        $returned = $entity->reorder(3);

        self::assertSame($entity, $returned);
        self::assertSame(3, $entity->sortOrder);
    }

    #[Test]
    public function reorderReplacesThePreviousPosition(): void
    {
        $entity = new OrderedEntity();

        $entity->reorder(7)->reorder(2);

        self::assertSame(2, $entity->sortOrder);
    }

    #[Test]
    public function thePositionIsAnIntegerColumnNamedSortOrderThatDefaultsToZero(): void
    {
        $columns = new ReflectionProperty(OrderedEntity::class, 'sortOrder')->getAttributes(Column::class);

        self::assertCount(1, $columns);
        $column = $columns[0]->newInstance();
        self::assertSame('sort_order', $column->name);
        self::assertSame('integer', $column->type);
        self::assertSame(['default' => 0], $column->options);
    }
}
