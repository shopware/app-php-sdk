<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Test\MockShop;
use Shopware\App\SDK\Test\MockShopRepository;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockShopRepository::class)]
#[CoversClass(MockShop::class)]
class MockShopRepositoryTest extends TestCase
{
    private MockShopRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new MockShopRepository();
    }

    public function testLifecycle(): void
    {
        static::assertNull($this->repository->getShopFromId('1'));

        $shop = $this->repository->createShopFromArray('1', 'asd', 'test');

        $this->repository->createShop($shop);

        static::assertSame($shop, $this->repository->getShopFromId('1'));

        $this->repository->deleteShop($shop);

        static::assertNull($this->repository->getShopFromId('1'));
    }
}
