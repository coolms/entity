<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Traits;

use CoolMS\Entity\Tests\Fixture\TaggableEntity;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * TaggableExtrasProviderTrait tags an entity through its extras bag, with no
 * foreign key to whatever owns the tags.
 *
 * Two kinds of tag are kept apart: taxonomy node ids in extras['taxonomy'] and
 * plain string tags in extras['tags']. Each list holds a value once, keeps the
 * order values were added in, and stays a list (keys 0..n-1) after a removal.
 * The fixture is the shape a consumer writes: the extras trait plus this one.
 */
final class TaggableExtrasProviderTraitTest extends TestCase
{
    private const string NODE_A = '0199a1b2-0000-7000-8000-00000000000a';

    private const string NODE_B = '0199a1b2-0000-7000-8000-00000000000b';

    #[Test]
    public function aNewEntityHasNoTagsOfEitherKind(): void
    {
        $entity = new TaggableEntity();

        self::assertSame([], $entity->getTags());
        self::assertSame([], $entity->getTaxonomyTagIds());
    }

    #[Test]
    public function aTagAddedTwiceIsHeldOnceInItsFirstPosition(): void
    {
        $entity = new TaggableEntity();

        $entity->addTag('featured')->addTag('new')->addTag('featured');

        self::assertSame(['featured', 'new'], $entity->getTags());
    }

    #[Test]
    public function removingATagLeavesAListInTheOriginalOrder(): void
    {
        $entity = new TaggableEntity();
        $entity->addTag('featured')->addTag('new')->addTag('sale');

        $entity->removeTag('new');

        self::assertSame(['featured', 'sale'], $entity->getTags());
    }

    #[Test]
    public function removingATagThatIsNotThereChangesNothing(): void
    {
        $entity = new TaggableEntity();
        $entity->addTag('featured');

        $entity->removeTag('archived');

        self::assertSame(['featured'], $entity->getTags());
    }

    #[Test]
    public function taxonomyTagsAreHeldOnceAndRemovedLikePlainTags(): void
    {
        $entity = new TaggableEntity();

        $entity->addTaxonomyTag(self::NODE_A)->addTaxonomyTag(self::NODE_B)->addTaxonomyTag(self::NODE_A);
        self::assertSame([self::NODE_A, self::NODE_B], $entity->getTaxonomyTagIds());

        $entity->removeTaxonomyTag(self::NODE_A);
        self::assertSame([self::NODE_B], $entity->getTaxonomyTagIds());
    }

    #[Test]
    public function theTwoKindsAreStoredUnderTheirOwnKeysInTheExtrasBag(): void
    {
        $entity = new TaggableEntity();

        $entity->addTaxonomyTag(self::NODE_A)->addTag('featured');

        self::assertSame(['taxonomy' => [self::NODE_A], 'tags' => ['featured']], $entity->extras);
        self::assertSame(['featured'], $entity->getTags());
        self::assertSame([self::NODE_A], $entity->getTaxonomyTagIds());
    }

    #[Test]
    public function tagsAlreadyInTheBagAreReadBack(): void
    {
        // The shape of an entity loaded from storage: the bag arrives whole.
        $entity = new TaggableEntity();
        $entity->extras = ['tags' => ['imported'], 'taxonomy' => [self::NODE_B]];

        self::assertSame(['imported'], $entity->getTags());
        self::assertSame([self::NODE_B], $entity->getTaxonomyTagIds());
    }

    #[Test]
    public function everyChangeReturnsTheSameEntity(): void
    {
        $entity = new TaggableEntity();

        self::assertSame($entity, $entity->addTag('featured'));
        self::assertSame($entity, $entity->removeTag('featured'));
        self::assertSame($entity, $entity->addTaxonomyTag(self::NODE_A));
        self::assertSame($entity, $entity->removeTaxonomyTag(self::NODE_A));
    }
}
