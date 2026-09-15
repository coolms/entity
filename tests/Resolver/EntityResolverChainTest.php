<?php

declare(strict_types=1);

namespace CoolMS\Entity\Tests\Resolver;

use CoolMS\Entity\Resolver\EntityResolverChain;
use CoolMS\Entity\Resolver\EntityResolverInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
final class EntityResolverChainTest extends TestCase
{
    public function testResolveReturnsNullWhenNoResolverSupports(): void
    {
        $r1 = $this->createMock(EntityResolverInterface::class);
        $r1->method('supports')->willReturn(false);
        $r1->expects(self::never())->method('resolve');

        $chain = new EntityResolverChain([$r1]);
        self::assertNull($chain->resolve('Acme\Foo', '01H'));
    }

    public function testResolveDispatchesToFirstSupportingResolver(): void
    {
        $r1 = $this->createMock(EntityResolverInterface::class);
        $r1->method('supports')->willReturn(false);
        $r1->expects(self::never())->method('resolve');

        $r2 = $this->createMock(EntityResolverInterface::class);
        $r2->method('supports')->willReturn(true);
        $r2->expects(self::once())->method('resolve')->with('Acme\Foo', 'id-1', null)
            ->willReturn(['name' => 'Alice']);

        $r3 = $this->createMock(EntityResolverInterface::class);
        $r3->expects(self::never())->method('supports');

        $chain = new EntityResolverChain([$r1, $r2, $r3]);
        self::assertSame(['name' => 'Alice'], $chain->resolve('Acme\Foo', 'id-1'));
    }

    public function testResolveForwardsFieldsAllowList(): void
    {
        $r = $this->createMock(EntityResolverInterface::class);
        $r->method('supports')->willReturn(true);
        $r->expects(self::once())->method('resolve')
            ->with('Acme\Foo', 'id-1', ['name', 'email'])
            ->willReturn(['name' => 'Alice', 'email' => 'a@x']);

        $chain = new EntityResolverChain([$r]);
        self::assertSame(
            ['name' => 'Alice', 'email' => 'a@x'],
            $chain->resolve('Acme\Foo', 'id-1', ['name', 'email']),
        );
    }

    public function testSearchReturnsEmptyWhenNoResolverSupports(): void
    {
        $r = $this->createMock(EntityResolverInterface::class);
        $r->method('supports')->willReturn(false);

        $chain = new EntityResolverChain([$r]);
        self::assertSame([], $chain->search('Acme\Foo', 'q'));
    }

    public function testSearchDispatchesAndReturnsRowList(): void
    {
        $rows = [
            ['id' => '01H', 'label' => 'Alice'],
            ['id' => '02H', 'label' => 'Bob', 'secondary' => 'bob@x'],
        ];
        $r = $this->createMock(EntityResolverInterface::class);
        $r->method('supports')->willReturn(true);
        $r->expects(self::once())->method('search')
            ->with('Acme\Foo', 'q', 5)
            ->willReturn($rows);

        $chain = new EntityResolverChain([$r]);
        self::assertSame($rows, $chain->search('Acme\Foo', 'q', 5));
    }
}
