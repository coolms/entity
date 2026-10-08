<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Core\Mapping\Column;
use CoolMS\Entity\Tests\Fixture\NamedEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * NameProviderTrait gives an entity a technical `name`: a string column that
 * every serializer group reads and the `write` group accepts.
 *
 * The trait's whole contract is that property and its two attributes, so the
 * attributes are asserted as behaviour. The fixture is the simplest shape a
 * consumer writes: the trait's constructor is the entity's constructor.
 */
final class NameProviderTraitTest extends TestCase
{
    #[Test]
    public function theConstructorArgumentBecomesTheName(): void
    {
        self::assertSame('invoice', new NamedEntity('invoice')->name);
    }

    #[Test]
    public function theNameCanBeChangedAfterConstruction(): void
    {
        $entity = new NamedEntity('invoice');

        $entity->name = 'credit_note';

        self::assertSame('credit_note', $entity->name);
    }

    #[Test]
    public function theNameIsAStringColumn(): void
    {
        $columns = new ReflectionProperty(NamedEntity::class, 'name')->getAttributes(Column::class);

        self::assertCount(1, $columns);
        self::assertSame('string', $columns[0]->newInstance()->type);
    }

    #[Test]
    public function theNameIsReadInEveryGroupAndAcceptedOnWrite(): void
    {
        $groups = new ReflectionProperty(NamedEntity::class, 'name')->getAttributes(Groups::class);

        self::assertCount(1, $groups);
        self::assertSame(['read', 'list', 'search', 'stat', 'write'], $groups[0]->newInstance()->groups);
    }
}
