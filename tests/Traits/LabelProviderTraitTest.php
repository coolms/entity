<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Core\Mapping\Column;
use CoolMS\Entity\Tests\Fixture\LabelledEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * LabelProviderTrait gives an entity a human-readable `label`: a string
 * column that every serializer group reads and the `write` group accepts.
 *
 * The trait's whole contract is that property and its two attributes, so the
 * attributes are asserted as behaviour. The fixture is the shape a consumer
 * writes when it has a constructor of its own.
 */
final class LabelProviderTraitTest extends TestCase
{
    #[Test]
    public function theConstructorArgumentBecomesTheLabel(): void
    {
        $entity = new LabelledEntity('Featured', 'featured');

        self::assertSame('Featured', $entity->label);
        self::assertSame('featured', $entity->slug);
    }

    #[Test]
    public function anEntityBuiltWithoutALabelHasAnEmptyOne(): void
    {
        self::assertSame('', new LabelledEntity()->label);
    }

    #[Test]
    public function theLabelCanBeChangedAfterConstruction(): void
    {
        $entity = new LabelledEntity('Featured');

        $entity->label = 'Editor\'s pick';

        self::assertSame('Editor\'s pick', $entity->label);
    }

    #[Test]
    public function theLabelIsAStringColumn(): void
    {
        $columns = new ReflectionProperty(LabelledEntity::class, 'label')->getAttributes(Column::class);

        self::assertCount(1, $columns);
        self::assertSame('string', $columns[0]->newInstance()->type);
    }

    #[Test]
    public function theLabelIsReadInEveryGroupAndAcceptedOnWrite(): void
    {
        $groups = new ReflectionProperty(LabelledEntity::class, 'label')->getAttributes(Groups::class);

        self::assertCount(1, $groups);
        self::assertSame(['read', 'list', 'search', 'stat', 'write'], $groups[0]->newInstance()->groups);
    }
}
