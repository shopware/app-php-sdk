<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Adapter\DynamoDB;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Result\GetItemOutput;
use AsyncAws\DynamoDb\ValueObject\AttributeValue;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Adapter\DynamoDB\DynamoDBRepository;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Adapter\DynamoDB\DynamoDBShop;

#[CoversClass(DynamoDBRepository::class)]
class DynamoDBRepositoryTest extends TestCase
{
    public function testCreateStruct(): void
    {
        $repository = new DynamoDBRepository($this->createMock(DynamoDbClient::class), 'tableName');

        $shop = $repository->createShopStruct('shopId', 'shopUrl', 'shopSecret');

        static::assertSame('shopId', $shop->getShopId());
        static::assertSame('shopUrl', $shop->getShopUrl());
        static::assertSame('shopSecret', $shop->getShopSecret());
        static::assertNull($shop->getShopClientId());
        static::assertNull($shop->getShopClientSecret());
        static::assertFalse($shop->isShopActive());
    }

    public function testCreateShop(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::exactly(2))
            ->method('putItem')
            ->with(static::callback(function (PutItemInput $input) {
                static::assertSame('tableName', $input->getTableName());

                $attributes = $input->getItem();

                static::assertArrayHasKey('id', $attributes);
                static::assertArrayHasKey('url', $attributes);
                static::assertArrayHasKey('secret', $attributes);
                static::assertArrayHasKey('clientId', $attributes);
                static::assertArrayHasKey('clientSecret', $attributes);
                static::assertArrayHasKey('active', $attributes);

                static::assertSame('shopId', $attributes['id']->getS());
                static::assertSame('shopUrl', $attributes['url']->getS());
                static::assertSame('shopSecret', $attributes['secret']->getS());
                static::assertSame('shopClientId', $attributes['clientId']->getS());
                static::assertSame('shopClientSecret', $attributes['clientSecret']->getS());
                static::assertSame(true, $attributes['active']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'shopClientId', 'shopClientSecret', true);
        $repository->createShop($shop);
        $repository->updateShop($shop);
    }

    public function testCreateShopNullClientId(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::once())
            ->method('putItem')
            ->with(static::callback(function (PutItemInput $input) {
                static::assertSame('tableName', $input->getTableName());

                $attributes = $input->getItem();

                static::assertArrayHasKey('id', $attributes);
                static::assertArrayHasKey('url', $attributes);
                static::assertArrayHasKey('secret', $attributes);
                static::assertArrayHasKey('clientId', $attributes);
                static::assertArrayHasKey('clientSecret', $attributes);
                static::assertArrayHasKey('active', $attributes);

                static::assertSame('shopId', $attributes['id']->getS());
                static::assertSame('shopUrl', $attributes['url']->getS());
                static::assertSame('shopSecret', $attributes['secret']->getS());
                static::assertSame('', $attributes['clientId']->getS());
                static::assertSame('', $attributes['clientSecret']->getS());
                static::assertFalse($attributes['active']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret');
        $repository->createShop($shop);
    }

    public function testGetShopNotFound(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $output = $this->createMock(GetItemOutput::class);
        $output
            ->expects(static::once())
            ->method('getItem')
            ->willReturn([]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->with(static::callback(function (GetItemInput $input) {
                static::assertSame('tableName', $input->getTableName());
                static::assertSame('shopId', $input->getKey()['id']->getS());

                return true;
            }))
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = $repository->getShopFromId('shopId');

        static::assertNull($shop);
    }


    public function testGetShopFound(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $output = $this->createMock(GetItemOutput::class);
        $output
            ->expects(static::once())
            ->method('getItem')
            ->willReturn([
                'id' => new AttributeValue(['S' => 'shopId']),
                'url' => new AttributeValue(['S' => 'shopUrl']),
                'secret' => new AttributeValue(['S' => 'shopSecret']),
                'clientId' => new AttributeValue(['S' => '']),
                'clientSecret' => new AttributeValue(['S' => '']),
                'active' => new AttributeValue(['BOOL' => false]),
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = $repository->getShopFromId('shopId');

        static::assertNotNull($shop);

        static::assertSame('shopId', $shop->getShopId());
        static::assertSame('shopUrl', $shop->getShopUrl());
        static::assertSame('shopSecret', $shop->getShopSecret());
        static::assertNull($shop->getShopClientId());
        static::assertNull($shop->getShopClientSecret());
        static::assertFalse($shop->isShopActive());
    }

    public function testGetShopFoundWithoutActive(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $output = $this->createMock(GetItemOutput::class);
        $output
            ->expects(static::once())
            ->method('getItem')
            ->willReturn([
                'id' => new AttributeValue(['S' => 'shopId']),
                'url' => new AttributeValue(['S' => 'shopUrl']),
                'secret' => new AttributeValue(['S' => 'shopSecret']),
                'clientId' => new AttributeValue(['S' => '']),
                'clientSecret' => new AttributeValue(['S' => '']),
                'active' => new AttributeValue(['BOOL' => null]),
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = $repository->getShopFromId('shopId');

        static::assertNotNull($shop);

        static::assertSame('shopId', $shop->getShopId());
        static::assertSame('shopUrl', $shop->getShopUrl());
        static::assertSame('shopSecret', $shop->getShopSecret());
        static::assertNull($shop->getShopClientId());
        static::assertNull($shop->getShopClientSecret());
        static::assertFalse($shop->isShopActive());
    }

    public function testDeleteShop(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::once())
            ->method('deleteItem')
            ->with(static::callback(function (DeleteItemInput $input) {
                static::assertSame('tableName', $input->getTableName());
                static::assertSame('shopId', $input->getKey()['id']->getS());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $repository->deleteShop('shopId');
    }
}
