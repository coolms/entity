<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Core\Attribute\FieldMeta;
use CoolMS\Core\Mapping\Column;
use CoolMS\Entity\Exception\ReservedPropertyException;
use CoolMS\Entity\Tests\Fixture\ExtrasEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * ExtrasProviderTrait gives an entity a JSON `extras` bag for fields that are
 * not declared as properties: dynamic fields defined at runtime.
 *
 * The bag is reached three ways -- magic properties, array access and the
 * explicit getExtra()/setExtra() pair -- and all three read and write the same
 * array. The fixture is the shape a consumer writes.
 */
final class ExtrasProviderTraitTest extends TestCase
{
    #[Test]
    public function aNewEntityStartsWithAnEmptyBag(): void
    {
        $entity = new ExtrasEntity();

        self::assertSame([], $entity->extras);
        self::assertNull($entity->bio);
        self::assertNull($entity->getExtra('bio'));
        self::assertNull($entity['bio']);
        self::assertFalse(isset($entity['bio']));
    }

    #[Test]
    public function anUndeclaredPropertyIsReadAndWrittenThroughTheBag(): void
    {
        $entity = new ExtrasEntity();

        $entity->bio = 'Writes about birds.';

        self::assertSame(['bio' => 'Writes about birds.'], $entity->extras);
        self::assertSame('Writes about birds.', $entity->bio);
    }

    #[Test]
    public function aDeclaredPropertyNeverGoesThroughTheBag(): void
    {
        $entity = new ExtrasEntity();

        $entity->title = 'Field notes';

        self::assertSame('Field notes', $entity->title);
        self::assertSame([], $entity->extras);
    }

    #[Test]
    public function arrayAccessReadsAndWritesTheSameBag(): void
    {
        $entity = new ExtrasEntity();

        $entity['colour'] = 'red';

        self::assertSame(['colour' => 'red'], $entity->extras);
        self::assertSame('red', $entity->getExtra('colour'));
        self::assertSame('red', $entity['colour']);

        unset($entity['colour']);

        self::assertSame([], $entity->extras);
    }

    #[Test]
    public function aKeyHoldingNullStillExists(): void
    {
        $entity = new ExtrasEntity();

        $entity['note'] = null;

        // offsetExists() asks whether the key is in the bag, not whether its
        // value is set, so a field cleared to null is still a known field.
        self::assertTrue(isset($entity['note']));
        self::assertNull($entity['note']);
        self::assertSame(['note' => null], $entity->extras);
    }

    #[Test]
    public function aPropertyPathInBracketsReachesTheBag(): void
    {
        $entity = new ExtrasEntity();
        $accessor = PropertyAccess::createPropertyAccessor();

        $accessor->setValue($entity, '[rating]', 4);

        self::assertSame(['rating' => 4], $entity->extras);
        self::assertSame(4, $accessor->getValue($entity, '[rating]'));
    }

    #[Test]
    public function theBagItselfCannotBeReplacedByWritingAFieldNamedExtras(): void
    {
        $entity = new ExtrasEntity();
        $entity['colour'] = 'red';

        try {
            $entity['extras'] = [];
            self::fail('Writing a field named "extras" must be refused.');
        } catch (ReservedPropertyException $refused) {
            self::assertStringContainsString('"extras"', $refused->getMessage());
            self::assertStringContainsString(ExtrasEntity::class, $refused->getMessage());
        }

        self::assertSame(['colour' => 'red'], $entity->extras);
    }

    #[Test]
    public function setExtraWritesOneKeyAndLeavesTheOthers(): void
    {
        $entity = new ExtrasEntity();

        $entity->setExtra('width', 640);
        $entity->setExtra('height', 480);
        $entity->setExtra('width', 800);

        self::assertSame(['width' => 800, 'height' => 480], $entity->extras);
        self::assertSame(800, $entity->getExtra('width'));
    }

    #[Test]
    public function theBagIsAPrivateNonNullJsonColumnThatDefaultsToAnEmptyObject(): void
    {
        $property = new ReflectionProperty(ExtrasEntity::class, 'extras');

        $columns = $property->getAttributes(Column::class);
        self::assertCount(1, $columns);
        $column = $columns[0]->newInstance();
        self::assertSame('extras', $column->name);
        self::assertSame('json', $column->type);
        self::assertFalse($column->nullable);
        self::assertSame(['default' => '{}'], $column->options);

        $meta = $property->getAttributes(FieldMeta::class);
        self::assertCount(1, $meta);
        self::assertTrue($meta[0]->newInstance()->private);
    }
}
