<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Resolver;

use CoolMS\Entity\Field\EntityFieldDescriptorInterface;
use CoolMS\Entity\Registry\FieldExtractorInterface;
use CoolMS\Entity\Registry\RepositoryRegistryInterface;
use CoolMS\Entity\Resolver\DefaultEntityResolver;
use CoolMS\Entity\Search\FreeTextProjector;
use CoolMS\Entity\Security\RecordReadGuardInterface;
use CoolMS\Rql\RqlContext;
use CoolMS\Rql\RqlQuery;
use CoolMS\Rql\RqlRepositoryInterface;
use CoolMS\Rql\RqlResult;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Every record the default resolver resolves is asked of the read guard once loaded, and before anything of it is
 * extracted: a refused record resolves to null, exactly as a missing one does, and a readable one to the fields the
 * guard allows and no other. A resolver given no guard reads no record at all.
 */
final class DefaultEntityResolverAsksTheGuardTest extends TestCase
{
    private stdClass $record;

    /** @var list<list<string>|null> the field lists the extractor was asked for */
    private array $extracted = [];

    #[Test]
    public function aResolverGivenNoGuardReadsNoRecord(): void
    {
        $resolver = new DefaultEntityResolver($this->repositories(), $this->extractor(), $this->projector());

        self::assertNull($resolver->resolve('Thing', 1), 'a record that exists resolves as a missing one');
        self::assertNull($resolver->resolve('Thing', 1, ['name']));
        self::assertSame([], $this->extracted, 'nothing of it was extracted');
    }

    #[Test]
    public function aRefusedRecordResolvesAsAMissingOneAndIsNeverExtracted(): void
    {
        $resolver = $this->resolverWith(null);

        self::assertNull($resolver->resolve('Thing', 1));
        self::assertNull($resolver->resolve('Thing', 1, ['name']));
        self::assertSame([], $this->extracted);
        self::assertNull($resolver->resolve('Thing', 2), 'a missing record is null too');
    }

    #[Test]
    public function aReadableRecordResolvesToTheAllowedFieldsOnly(): void
    {
        $resolver = $this->resolverWith(['id', 'name']);

        self::assertSame(['id' => 1, 'name' => 'n'], $resolver->resolve('Thing', 1), 'asked for none: every allowed one');
        self::assertSame(['name' => 'n'], $resolver->resolve('Thing', 1, ['name', 'secret']), 'a field not allowed is absent');
        self::assertSame([], $resolver->resolve('Thing', 1, ['secret']), 'nothing asked for is allowed');
        self::assertSame([['id', 'name'], ['name']], $this->extracted, 'the extractor is only ever asked for allowed fields');
    }

    /** @param list<string>|null $fields */
    public function wasAskedFor(?array $fields): void
    {
        $this->extracted[] = $fields;
    }

    protected function setUp(): void
    {
        $this->record = new stdClass();
    }

    /** @param list<string>|null $allowed */
    private function resolverWith(?array $allowed): DefaultEntityResolver
    {
        $guard = new readonly class($this->record, $allowed) implements RecordReadGuardInterface {
            /** @param list<string>|null $allowed */
            public function __construct(private object $record, private ?array $allowed)
            {
            }

            public function fieldsFor(object $record): ?array
            {
                return $record === $this->record ? $this->allowed : null;
            }

            public function predicateFieldsFor(string $class): array
            {
                return [];
            }
        };

        return new DefaultEntityResolver($this->repositories(), $this->extractor(), $this->projector(), $guard);
    }

    private function repositories(): RepositoryRegistryInterface
    {
        $repository = new readonly class($this->record) implements RqlRepositoryInterface {
            public function __construct(private object $record)
            {
            }

            public function findById(string|int $id): ?object
            {
                return 1 === $id ? $this->record : null;
            }

            public function findByRql(RqlQuery $query, RqlContext $context): RqlResult
            {
                throw new LogicException('not read by resolve()');
            }
        };
        $registry = $this->createStub(RepositoryRegistryInterface::class);
        $registry->method('has')->willReturn(true);
        $registry->method('get')->willReturn($repository);

        return $registry;
    }

    private function extractor(): FieldExtractorInterface
    {
        return new class($this) implements FieldExtractorInterface {
            private const array VALUES = ['id' => 1, 'name' => 'n', 'secret' => 's'];

            public function __construct(private readonly DefaultEntityResolverAsksTheGuardTest $test)
            {
            }

            public function extract(object $entity, ?array $fields = null): array
            {
                $this->test->wasAskedFor($fields);
                $out = [];
                foreach ($fields ?? array_keys(self::VALUES) as $field) {
                    $out[$field] = self::VALUES[$field];
                }

                return $out;
            }

            public function extractLabel(object $entity): string
            {
                return 'label';
            }

            public function extractSecondary(object $entity): ?string
            {
                return null;
            }

            public function extractId(object $entity): int
            {
                return 1;
            }
        };
    }

    private function projector(): FreeTextProjector
    {
        return new FreeTextProjector($this->createStub(EntityFieldDescriptorInterface::class));
    }
}
