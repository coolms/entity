<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Core\Mapping\Column;
use CoolMS\Entity\Tests\Fixture\DescribedEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * DescriptionProviderTrait gives an entity a human-readable `description`: a
 * string column that every serializer group reads and the `write` group
 * accepts.
 *
 * The trait's whole contract is that property and its two attributes, so the
 * attributes are asserted as behaviour. The fixture is the simplest shape a
 * consumer writes: the trait's constructor is the entity's constructor.
 */
final class DescriptionProviderTraitTest extends TestCase
{
    #[Test]
    public function theConstructorArgumentBecomesTheDescription(): void
    {
        self::assertSame('Issued monthly', new DescribedEntity('Issued monthly')->description);
    }

    #[Test]
    public function theDescriptionCanBeChangedAfterConstruction(): void
    {
        $entity = new DescribedEntity('Issued monthly');

        $entity->description = 'Issued quarterly';

        self::assertSame('Issued quarterly', $entity->description);
    }

    #[Test]
    public function theDescriptionIsAStringColumn(): void
    {
        $columns = new ReflectionProperty(DescribedEntity::class, 'description')->getAttributes(Column::class);

        self::assertCount(1, $columns);
        self::assertSame('string', $columns[0]->newInstance()->type);
    }

    #[Test]
    public function theDescriptionIsReadInEveryGroupAndAcceptedOnWrite(): void
    {
        $groups = new ReflectionProperty(DescribedEntity::class, 'description')->getAttributes(Groups::class);

        self::assertCount(1, $groups);
        self::assertSame(['read', 'list', 'search', 'stat', 'write'], $groups[0]->newInstance()->groups);
    }
}
