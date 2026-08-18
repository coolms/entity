<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Service;

use CoolMS\Entity\Contract\FieldSchemaSourceInterface;
use CoolMS\Entity\Registry\EntityAliasRegistry;
use CoolMS\Entity\Service\EntitySchemaLookup;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Entity's own schema logic: instance-aware `appliesTo` filtering, and the
 * merge order between static config and the runtime field source.
 *
 * Turning stored definitions INTO the neutral shape belongs to whichever module
 * stores them -- see FieldSchemaSourceTest for that half.
 */
final class EntitySchemaLookupTest extends TestCase
{
    // -- getSchemaForInstance: filtering ------------------------------------

    #[Test]
    public function getSchemaForInstanceIncludesFieldWhenAppliesToMatches(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['instanceNameSuffix' => ['appliesTo' => ['mimeType' => 'text/x-dtmpl', 'type' => 'file']]]],
            [FakeNode::class => 'vfs_node'],
        );

        $node = new FakeNode(mimeType: 'text/x-dtmpl', type: 'file');

        self::assertArrayHasKey('instanceNameSuffix', $lookup->getSchemaForInstance($node));
    }

    #[Test]
    public function getSchemaForInstanceExcludesFieldWhenOneAppliesToKeyMismatches(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['instanceNameSuffix' => ['appliesTo' => ['mimeType' => 'text/x-dtmpl', 'type' => 'file']]]],
            [FakeNode::class => 'vfs_node'],
        );

        $node = new FakeNode(mimeType: 'image/jpeg', type: 'file');

        self::assertArrayNotHasKey('instanceNameSuffix', $lookup->getSchemaForInstance($node));
    }

    #[Test]
    public function getSchemaForInstanceIncludesFieldWithNullAppliesTo(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['title' => ['appliesTo' => null]]],
            [FakeNode::class => 'vfs_node'],
        );

        $node = new FakeNode(mimeType: 'image/jpeg', type: 'file');

        self::assertArrayHasKey('title', $lookup->getSchemaForInstance($node));
    }

    #[Test]
    public function getSchemaForInstanceExcludesFieldWhenPropertyMissing(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['phantom' => ['appliesTo' => ['nonExistentProperty' => 'whatever']]]],
            [FakeNode::class => 'vfs_node'],
        );

        $node = new FakeNode(mimeType: 'text/x-dtmpl', type: 'file');

        self::assertArrayNotHasKey('phantom', $lookup->getSchemaForInstance($node));
    }

    #[Test]
    public function getSchemaForInstanceUnwrapsBackedEnumForComparison(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['instanceNameSuffix' => ['appliesTo' => ['type' => 'file']]]],
            [FakeNodeWithEnum::class => 'vfs_node'],
        );

        $node = new FakeNodeWithEnum(type: FakeNodeType::File);

        self::assertArrayHasKey(
            'instanceNameSuffix',
            $lookup->getSchemaForInstance($node),
            'Backed enum value should match its scalar `value` ("file")',
        );
    }

    #[Test]
    public function getSchemaForInstanceReturnsEmptyWhenClassNotRegistered(): void
    {
        $lookup = $this->makeLookup(['vfs_node' => ['title' => []]], []);

        self::assertSame([], $lookup->getSchemaForInstance(new FakeNode(mimeType: 'x', type: 'x')));
    }

    // -- getSchemaForInstance: array-value appliesTo (OR semantics) ---------

    #[Test]
    public function getSchemaForInstanceMatchesAnyValueInArrayForMimeType(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['defaultOutputFormat' => ['appliesTo' => [
                'mimeType' => [
                    'text/x-dtmpl',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ],
                'type' => 'file',
            ]]]],
            [FakeNode::class => 'vfs_node'],
        );

        $dtmplNode = new FakeNode(mimeType: 'text/x-dtmpl', type: 'file');
        $docxNode = new FakeNode(
            mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            type: 'file',
        );

        self::assertArrayHasKey('defaultOutputFormat', $lookup->getSchemaForInstance($dtmplNode));
        self::assertArrayHasKey('defaultOutputFormat', $lookup->getSchemaForInstance($docxNode));
    }

    #[Test]
    public function getSchemaForInstanceRejectsWhenValueNotInArray(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['defaultOutputFormat' => ['appliesTo' => [
                'mimeType' => ['text/x-dtmpl', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'type' => 'file',
            ]]]],
            [FakeNode::class => 'vfs_node'],
        );

        $imageNode = new FakeNode(mimeType: 'image/jpeg', type: 'file');

        self::assertArrayNotHasKey('defaultOutputFormat', $lookup->getSchemaForInstance($imageNode));
    }

    #[Test]
    public function getSchemaForInstanceEmptyArrayValueMatchesNothing(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['never' => ['appliesTo' => ['mimeType' => []]]]],
            [FakeNode::class => 'vfs_node'],
        );

        $anyNode = new FakeNode(mimeType: 'text/x-dtmpl', type: 'file');

        self::assertArrayNotHasKey('never', $lookup->getSchemaForInstance($anyNode));
    }

    #[Test]
    public function getSchemaForInstanceSingleStringStillUsesExactMatch(): void
    {
        // Regression guard for backward compatibility with the original (pre-array) form.
        $lookup = $this->makeLookup(
            ['vfs_node' => ['legacy' => ['appliesTo' => ['mimeType' => 'text/x-dtmpl']]]],
            [FakeNode::class => 'vfs_node'],
        );

        self::assertArrayHasKey(
            'legacy',
            $lookup->getSchemaForInstance(new FakeNode(mimeType: 'text/x-dtmpl', type: 'file')),
        );
        self::assertArrayNotHasKey(
            'legacy',
            $lookup->getSchemaForInstance(new FakeNode(mimeType: 'image/jpeg', type: 'file')),
        );
    }

    #[Test]
    public function getSchemaForInstanceCombinesArrayKeyWithScalarKeyViaAND(): void
    {
        $lookup = $this->makeLookup(
            ['vfs_node' => ['docField' => ['appliesTo' => [
                'mimeType' => ['text/x-dtmpl', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'type' => 'file',
            ]]]],
            [FakeNode::class => 'vfs_node'],
        );

        // Matching mime, wrong type -> still rejected (AND).
        $directoryNode = new FakeNode(mimeType: 'text/x-dtmpl', type: 'directory');

        self::assertArrayNotHasKey('docField', $lookup->getSchemaForInstance($directoryNode));
    }

    // -- the port itself ----------------------------------------------------

    #[Test]
    public function resolvesWithoutATypeContributorAtAll(): void
    {
        // The point of the ports: extras works in an install with no
        // runtime-type module. Every consumer of ExtrasProviderInterface --
        // VFS, Identity, Content, Workflow -- depends on this staying true.
        $lookup = $this->makeLookup(['vfs_node' => ['title' => ['type' => 'text']]], []);

        self::assertSame(['title' => ['type' => 'text']], $lookup->getSchemaForEntity('vfs_node'));
    }

    #[Test]
    public function schemaIsCachedPerAliasSoTheSourceIsAskedOnce(): void
    {
        $calls = 0;
        $source = new class($calls) implements FieldSchemaSourceInterface {
            public function __construct(public int &$calls)
            {
            }

            public function getRuntimeFields(string $entityAlias): array
            {
                ++$this->calls;

                return ['title' => ['type' => 'text']];
            }
        };

        $lookup = new EntitySchemaLookup($source, '', '', null, new EntityAliasRegistry([]));
        $lookup->getSchemaForEntity('vfs_node');
        $lookup->getSchemaForEntity('vfs_node');

        self::assertSame(1, $calls);

        $lookup->invalidate('vfs_node');
        $lookup->getSchemaForEntity('vfs_node');

        self::assertSame(2, $calls, 'invalidate() must drop the cached entry');
    }

    // -- Helpers ------------------------------------------------------------

    /**
     * @param array<string, array<string, array<string, mixed>>> $fieldsByAlias
     */
    private function makeFieldSource(array $fieldsByAlias): FieldSchemaSourceInterface
    {
        $source = $this->createStub(FieldSchemaSourceInterface::class);
        $source->method('getRuntimeFields')->willReturnCallback(
            static fn (string $alias): array => $fieldsByAlias[$alias] ?? [],
        );

        return $source;
    }

    /**
     * @param array<string, array<string, array<string, mixed>>> $fieldsByAlias
     * @param array<class-string, string>                        $aliasMap
     */
    private function makeLookup(array $fieldsByAlias, array $aliasMap): EntitySchemaLookup
    {
        return new EntitySchemaLookup(
            fieldSchemaSource: $this->makeFieldSource($fieldsByAlias),
            configDir: '',
            modulesDir: '',
            typeContributor: null,
            aliasRegistry: new EntityAliasRegistry($aliasMap),
        );
    }
}

/**
 * @internal test-only ExtrasProvider-shaped stub
 */
final class FakeNode
{
    public function __construct(
        public string $mimeType,
        public string $type,
    ) {
    }
}

/**
 * @internal test-only stub exposing a backed-enum property
 */
final class FakeNodeWithEnum
{
    public function __construct(
        public FakeNodeType $type,
    ) {
    }
}

enum FakeNodeType: string
{
    case File = 'file';
    case Directory = 'directory';
}
