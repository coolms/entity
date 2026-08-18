<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Search;

use CoolMS\Entity\Field\EntityFieldDescriptorInterface;
use CoolMS\Entity\Field\FieldDescriptor;
use CoolMS\Entity\Search\FreeTextProjector;
use CoolMS\Rql\AndNode;
use CoolMS\Rql\FilterNode;
use CoolMS\Rql\FilterOp;
use CoolMS\Rql\OrNode;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the free-text RQL projection service.
 */
final class FreeTextProjectorTest extends TestCase
{
    public function testProjectReturnsNullOnEmptyQuery(): void
    {
        $projector = $this->makeProjector(['name', 'description']);

        self::assertNull($projector->project(SomeEntity::class, ''));
    }

    public function testProjectReturnsNullWhenEntityHasNoSearchableFields(): void
    {
        $projector = $this->makeProjector([]);

        self::assertNull($projector->project(SomeEntity::class, 'query'));
    }

    public function testProjectBuildsOrNodeWithCnFilterPerSearchableField(): void
    {
        $projector = $this->makeProjector(['name']);

        $result = $projector->project(SomeEntity::class, 'foo');

        self::assertInstanceOf(OrNode::class, $result);
        self::assertCount(1, $result->nodes);
        $node = self::assertLeafFilter($result->nodes[0]);
        self::assertSame('name', $node->field);
        self::assertSame(FilterOp::Cn, $node->op);
        self::assertSame('foo', $node->value);
    }

    public function testProjectProducesMultipleOrEntriesForMultipleSearchableFields(): void
    {
        $projector = $this->makeProjector(['title', 'slug', 'body']);

        $result = $projector->project(SomeEntity::class, 'q');

        self::assertInstanceOf(OrNode::class, $result);
        self::assertCount(3, $result->nodes);
        $fields = array_map(
            static fn (FilterNode|OrNode|AndNode $n): string => self::assertLeafFilter($n)->field,
            $result->nodes,
        );
        self::assertSame(['title', 'slug', 'body'], $fields);
        foreach ($result->nodes as $child) {
            $node = self::assertLeafFilter($child);
            self::assertSame(FilterOp::Cn, $node->op);
            self::assertSame('q', $node->value);
        }
    }

    /**
     * Assert an RQL child node is a leaf {@see FilterNode} and return it narrowed.
     *
     * The AST child union is `FilterNode|OrNode|AndNode` (arbitrarily nestable),
     * but the projector only ever emits flat leaves — asserting that is part of
     * the contract under test. A plain `instanceof` guard (rather than
     * `assertInstanceOf()`) is what actually narrows the union for static
     * analysis, since phpstan-phpunit is not installed.
     */
    private static function assertLeafFilter(FilterNode|OrNode|AndNode $node): FilterNode
    {
        if (!$node instanceof FilterNode) {
            self::fail(sprintf('Expected a leaf FilterNode, got %s.', $node::class));
        }

        return $node;
    }

    /**
     * @param list<string> $searchableFieldNames
     */
    private function makeProjector(array $searchableFieldNames): FreeTextProjector
    {
        $descriptors = [];
        foreach ($searchableFieldNames as $name) {
            $descriptors[] = new FieldDescriptor(
                field: $name,
                label: ucfirst($name),
                type: 'string',
                filterable: false,
                filterOperators: [],
                sortable: false,
                searchable: true,
            );
        }
        // Add one non-searchable descriptor to verify filtering.
        $descriptors[] = new FieldDescriptor(
            field: 'private',
            label: 'Private',
            type: 'string',
            filterable: false,
            filterOperators: [],
            sortable: false,
            searchable: false,
        );

        $registry = $this->createStub(EntityFieldDescriptorInterface::class);
        $registry->method('describe')->willReturn($descriptors);

        return new FreeTextProjector($registry);
    }
}

/**
 * Local fixture class -- the projector only needs an FQCN.
 */
final class SomeEntity
{
}
