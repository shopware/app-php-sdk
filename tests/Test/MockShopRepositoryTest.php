<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Test;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Test\MockShopRepository;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockShopRepository::class)]
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

        $shop = $this->repository->createShopStruct('1', 'asd', 'test');

        $this->repository->createShop($shop);

        static::assertSame($shop, $this->repository->getShopFromId('1'));

        $shop->setShopUrl('https://example.org');

        $this->repository->updateShop($shop);

        static::assertSame('https://example.org', $this->repository->getShopFromId('1')?->getShopUrl());

        $this->repository->deleteShop($shop->getShopId());

        static::assertNull($this->repository->getShopFromId('1'));
    }
}
