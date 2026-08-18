<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\VirtualField;

use CoolMS\Core\Attribute\ClassMeta;
use CoolMS\Entity\Attribute\VirtualField;
use CoolMS\Entity\Filter\VirtualFieldDescriptor;
use CoolMS\Entity\Registry\EntityAliasRegistryInterface;
use CoolMS\Entity\VirtualField\VirtualFieldProviderInterface;
use CoolMS\Entity\VirtualField\VirtualFieldRegistry;
use CoolMS\Rql\FilterOp;
use PHPUnit\Framework\TestCase;

/**
 * Phase X-2.5b -- registry aggregation from attribute discovery
 * plus tagged providers.
 */
final class VirtualFieldRegistryTest extends TestCase
{
    public function testGetForEntityReturnsAttributeDiscoveredDescriptors(): void
    {
        $registry = $this->makeRegistry(providers: []);

        $descriptors = $registry->getForEntity('vfstub');

        self::assertCount(2, $descriptors);
        self::assertSame('daysSinceCreated', $descriptors[0]->name);
        self::assertSame('int', $descriptors[0]->filterType);
        self::assertTrue($descriptors[0]->hasSqlExpression());
        self::assertSame('upperName', $descriptors[1]->name);
        self::assertTrue($descriptors[1]->hasTranslator());
    }

    public function testNonAccessorMethodNameRetainedVerbatim(): void
    {
        $registry = $this->makeRegistry(providers: []);

        $descriptors = $registry->getForEntity('vfstub');
        $names = array_map(static fn (VirtualFieldDescriptor $d): string => $d->name, $descriptors);

        // `getDaysSinceCreated` becomes `daysSinceCreated`;
        // `upperName` (no get/is/has prefix) stays as-is.
        self::assertContains('daysSinceCreated', $names);
        self::assertContains('upperName', $names);
    }

    public function testProviderDescriptorsAppendedAfterAttributes(): void
    {
        $providerDescriptor = new VirtualFieldDescriptor(
            name: 'externalRank',
            label: 'External rank',
            filterType: 'int',
            sqlExpression: 'COALESCE(r.score, 0)',
        );
        $provider = new class($providerDescriptor) implements VirtualFieldProviderInterface {
            public function __construct(private readonly VirtualFieldDescriptor $descriptor)
            {
            }

            public function getTargetEntity(): string
            {
                return 'vfstub';
            }

            public function getDescriptors(): array
            {
                return [$this->descriptor];
            }
        };

        $registry = $this->makeRegistry(providers: [$provider]);

        $descriptors = $registry->getForEntity('vfstub');

        self::assertCount(3, $descriptors);
        self::assertSame('daysSinceCreated', $descriptors[0]->name);
        self::assertSame('upperName', $descriptors[1]->name);
        self::assertSame('externalRank', $descriptors[2]->name, 'Provider descriptors must follow attribute-discovered ones.');
    }

    public function testProvidersTargetingOtherEntityIgnored(): void
    {
        $unrelated = new class implements VirtualFieldProviderInterface {
            public function getTargetEntity(): string
            {
                return 'somethingElse';
            }

            public function getDescriptors(): array
            {
                return [
                    new VirtualFieldDescriptor(
                        name: 'unrelated',
                        label: 'Unrelated',
                        filterType: 'string',
                        sqlExpression: "'x'",
                    ),
                ];
            }
        };

        $registry = $this->makeRegistry(providers: [$unrelated]);

        $descriptors = $registry->getForEntity('vfstub');
        $names = array_map(static fn (VirtualFieldDescriptor $d): string => $d->name, $descriptors);

        self::assertNotContains('unrelated', $names);
    }

    public function testFindByNameReturnsMatchingDescriptor(): void
    {
        $registry = $this->makeRegistry(providers: []);

        $descriptor = $registry->findByName('vfstub', 'upperName');

        self::assertNotNull($descriptor);
        self::assertSame('upperName', $descriptor->name);
    }

    public function testFindByNameReturnsNullOnMiss(): void
    {
        $registry = $this->makeRegistry(providers: []);

        self::assertNull($registry->findByName('vfstub', 'nope'));
    }

    public function testUnknownAliasYieldsEmptyList(): void
    {
        $registry = $this->makeRegistry(providers: []);

        self::assertSame([], $registry->getForEntity('not-a-real-alias'));
    }

    /**
     * @param list<VirtualFieldProviderInterface> $providers
     */
    private function makeRegistry(array $providers): VirtualFieldRegistry
    {
        $aliasRegistry = $this->createStub(EntityAliasRegistryInterface::class);
        $aliasRegistry->method('resolve')->willReturnCallback(
            static fn (string $alias): ?string => 'vfstub' === $alias ? VfStubEntity::class : null,
        );

        return new VirtualFieldRegistry($providers, $aliasRegistry);
    }
}

/**
 * Test-only entity stub carrying both X- and Y-strategy
 * `#[VirtualField]` declarations for the registry's attribute
 * discoverer to find.
 */
#[ClassMeta(label: 'VF Stub', alias: 'vfstub')]
final class VfStubEntity
{
    // $qb is typed against the local stand-in below, not the real query
    // builder: coolms/entity must install with no persistence layer at all, and
    // that has to hold for its test suite too.
    public static function translateUpperName(FilterOp $op, mixed $value, TranslatorQueryBuilder $qb, string $paramName): void
    {
        $qb->andWhere("UPPER(e.name) = :{$paramName}")->setParameter($paramName, mb_strtoupper((string) $value));
    }

    #[VirtualField(
        label: 'Days since created',
        filterType: 'int',
        sqlExpression: 'DATE_DIFF(CURRENT_DATE(), e.createdAt)',
        allowedOps: ['eq', 'gt', 'lt'],
    )]
    public function getDaysSinceCreated(): int
    {
        return 0;
    }

    #[VirtualField(
        label: 'Upper-cased name',
        filterType: 'string',
        translatorMethod: 'translateUpperName',
        allowedOps: ['eq', 'cn'],
    )]
    public function upperName(): string
    {
        return '';
    }

    // No #[VirtualField] -- must be ignored by the discoverer.
    public function getOrdinaryProperty(): string
    {
        return '';
    }
}

/**
 * The shape a virtual-field translator receives from the filter applier that
 * lives in the persistence adapter package.
 *
 * Declared here as a minimal stand-in rather than imported: this package
 * declares no persistence dependency, so its suite must not need one either.
 * Only the two methods the fixture translator calls are named.
 *
 * @internal test use only
 */
interface TranslatorQueryBuilder
{
    public function andWhere(string $expr): static;

    public function setParameter(string $key, mixed $value): static;
}
