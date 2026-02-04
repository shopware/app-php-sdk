<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Adapter\DynamoDB;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Input\UpdateItemInput;
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
        static::assertFalse($shop->isRegistrationConfirmed());
        static::assertFalse($shop->hasVerifiedWithDoubleSignature());
    }

    public function testCreateShop(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::exactly(1))
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
                static::assertArrayHasKey('confirmed', $attributes);

                static::assertSame('shopId', $attributes['id']->getS());
                static::assertSame('shopUrl', $attributes['url']->getS());
                static::assertSame('shopSecret', $attributes['secret']->getS());
                static::assertSame('shopClientId', $attributes['clientId']->getS());
                static::assertSame('shopClientSecret', $attributes['clientSecret']->getS());
                static::assertSame(true, $attributes['active']->getBool());
                static::assertSame(false, $attributes['confirmed']->getBool());
                static::assertSame(false, $attributes['hasVerifiedWithDoubleSignature']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'shopClientId', 'shopClientSecret', true);
        $repository->createShop($shop);
    }

    public function testUpdateShop(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::exactly(1))
            ->method('updateItem')
            ->with(static::callback(function (UpdateItemInput $input) {
                static::assertSame('tableName', $input->getTableName());

                $key = $input->getKey();
                static::assertSame('shopId', $key['id']->getS());

                static::assertSame([
                    '#u' => 'url',
                ], $input->getExpressionAttributeNames());

                $attributes = $input->getExpressionAttributeValues();

                static::assertArrayHasKey(':url', $attributes);
                static::assertArrayHasKey(':secret', $attributes);
                static::assertArrayHasKey(':clientId', $attributes);
                static::assertArrayHasKey(':clientSecret', $attributes);
                static::assertArrayHasKey(':active', $attributes);
                static::assertArrayHasKey(':confirmed', $attributes);

                static::assertSame('shopUrl', $attributes[':url']->getS());
                static::assertSame('shopSecret', $attributes[':secret']->getS());
                static::assertSame('', $attributes[':clientId']->getS());
                static::assertSame('', $attributes[':clientSecret']->getS());
                static::assertSame(true, $attributes[':active']->getBool());
                static::assertSame(false, $attributes[':confirmed']->getBool());
                static::assertSame(true, $attributes[':hasVerifiedWithDoubleSignature']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', null, null, true, hasVerifiedWithDoubleSignature: true);
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
                static::assertArrayHasKey('confirmed', $attributes);

                static::assertSame('shopId', $attributes['id']->getS());
                static::assertSame('shopUrl', $attributes['url']->getS());
                static::assertSame('shopSecret', $attributes['secret']->getS());
                static::assertSame('', $attributes['clientId']->getS());
                static::assertSame('', $attributes['clientSecret']->getS());
                static::assertFalse($attributes['active']->getBool());
                static::assertFalse($attributes['confirmed']->getBool());

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
                'confirmed' => new AttributeValue(['BOOL' => false]),
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
        static::assertFalse($shop->isRegistrationConfirmed());
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

    public function testGetShopWithRotationFieldsPopulated(): void
    {
        $client = $this->createMock(DynamoDbClient::class);
        $timestamp = (new \DateTimeImmutable('2024-01-15 10:30:00'))->getTimestamp();

        $output = $this->createMock(GetItemOutput::class);
        $output
            ->expects(static::once())
            ->method('getItem')
            ->willReturn([
                'id' => new AttributeValue(['S' => 'shopId']),
                'url' => new AttributeValue(['S' => 'shopUrl']),
                'secret' => new AttributeValue(['S' => 'shopSecret']),
                'clientId' => new AttributeValue(['S' => 'clientId']),
                'clientSecret' => new AttributeValue(['S' => 'clientSecret']),
                'active' => new AttributeValue(['BOOL' => true]),
                'pendingShopSecret' => new AttributeValue(['S' => 'pending-secret']),
                'pendingShopUrl' => new AttributeValue(['S' => 'https://pending.com']),
                'previousShopSecret' => new AttributeValue(['S' => 'previous-secret']),
                'secretsRotatedAt' => new AttributeValue(['S' => (string)$timestamp]),
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');
        $shop = $repository->getShopFromId('shopId');

        static::assertNotNull($shop);
        static::assertSame('pending-secret', $shop->getPendingShopSecret());
        static::assertSame('https://pending.com', $shop->getPendingShopUrl());
        static::assertSame('previous-secret', $shop->getPreviousShopSecret());
        static::assertNotNull($shop->getSecretsRotatedAt());
        static::assertSame($timestamp, $shop->getSecretsRotatedAt()->getTimestamp());
        static::assertTrue($shop->isRegistrationConfirmed());
    }

    public function testGetShopConvertsEmptyStringsToNull(): void
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
                'pendingShopSecret' => new AttributeValue(['S' => '']),
                'pendingShopUrl' => new AttributeValue(['S' => '']),
                'previousShopSecret' => new AttributeValue(['S' => '']),
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');
        $shop = $repository->getShopFromId('shopId');

        static::assertNotNull($shop);
        // Empty strings from DynamoDB should be converted to null for optional fields
        static::assertNull($shop->getPendingShopSecret());
        static::assertNull($shop->getPendingShopUrl());
        static::assertNull($shop->getPreviousShopSecret());
        static::assertNull($shop->getSecretsRotatedAt());
        static::assertTrue($shop->isRegistrationConfirmed());
    }

    public function testCreateShopPersistsAllRotationFields(): void
    {
        $client = $this->createMock(DynamoDbClient::class);
        $timestamp = new \DateTimeImmutable('2024-01-15 10:30:00');

        $client
            ->expects(static::once())
            ->method('putItem')
            ->with(static::callback(function (PutItemInput $input) use ($timestamp) {
                $item = $input->getItem();

                // Verify all rotation fields are present in the item
                static::assertArrayHasKey('pendingShopSecret', $item);
                static::assertArrayHasKey('pendingShopUrl', $item);
                static::assertArrayHasKey('previousShopSecret', $item);
                static::assertArrayHasKey('secretsRotatedAt', $item);
                static::assertArrayHasKey('confirmed', $item);

                // Verify the values are correctly set
                static::assertSame('pending-secret', $item['pendingShopSecret']->getS());
                static::assertSame('https://pending.com', $item['pendingShopUrl']->getS());
                static::assertSame('previous-secret', $item['previousShopSecret']->getS());
                static::assertSame((string)$timestamp->getTimestamp(), $item['secretsRotatedAt']->getS());
                static::assertFalse($item['confirmed']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'clientId', 'clientSecret', true);
        $shop->setPendingShopSecret('pending-secret');
        $shop->setPendingShopUrl('https://pending.com');
        $shop->setPreviousShopSecret('previous-secret');
        $shop->setSecretsRotatedAt($timestamp);

        $repository->createShop($shop);
    }

    public function testCreateShopCastsNullRotationFieldsToEmptyStrings(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::once())
            ->method('putItem')
            ->with(static::callback(function (PutItemInput $input) {
                $item = $input->getItem();

                // Null values should be cast to empty strings for DynamoDB
                static::assertSame('', $item['pendingShopSecret']->getS());
                static::assertSame('', $item['pendingShopUrl']->getS());
                static::assertSame('', $item['previousShopSecret']->getS());
                static::assertSame('', $item['secretsRotatedAt']->getS());
                static::assertFalse($item['confirmed']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        // Shop with null rotation fields
        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'clientId', 'clientSecret', true);

        $repository->createShop($shop);
    }

    public function testUpdateShopPersistsAllRotationFields(): void
    {
        $client = $this->createMock(DynamoDbClient::class);
        $timestamp = new \DateTimeImmutable('2024-01-15 10:30:00');

        $client
            ->expects(static::once())
            ->method('updateItem')
            ->with(static::callback(function (UpdateItemInput $input) use ($timestamp) {
                $values = $input->getExpressionAttributeValues();

                // Verify all rotation fields are in the update expression values
                static::assertArrayHasKey(':pendingShopSecret', $values);
                static::assertArrayHasKey(':pendingShopUrl', $values);
                static::assertArrayHasKey(':previousShopSecret', $values);
                static::assertArrayHasKey(':secretsRotatedAt', $values);
                static::assertArrayHasKey(':confirmed', $values);

                // Verify the values are correctly set
                static::assertSame('pending-secret', $values[':pendingShopSecret']->getS());
                static::assertSame('https://pending.com', $values[':pendingShopUrl']->getS());
                static::assertSame('previous-secret', $values[':previousShopSecret']->getS());
                static::assertSame((string)$timestamp->getTimestamp(), $values[':secretsRotatedAt']->getS());
                static::assertFalse($values[':confirmed']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'clientId', 'clientSecret', true);
        $shop->setPendingShopSecret('pending-secret');
        $shop->setPendingShopUrl('https://pending.com');
        $shop->setPreviousShopSecret('previous-secret');
        $shop->setSecretsRotatedAt($timestamp);

        $repository->updateShop($shop);
    }

    public function testUpdateShopCastsNullRotationFieldsToEmptyStrings(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $client
            ->expects(static::once())
            ->method('updateItem')
            ->with(static::callback(function (UpdateItemInput $input) {
                $values = $input->getExpressionAttributeValues();

                // Null values should be cast to empty strings for DynamoDB
                static::assertSame('', $values[':pendingShopSecret']->getS());
                static::assertSame('', $values[':pendingShopUrl']->getS());
                static::assertSame('', $values[':previousShopSecret']->getS());
                static::assertSame('', $values[':secretsRotatedAt']->getS());
                static::assertFalse($values[':confirmed']->getBool());

                return true;
            }));

        $repository = new DynamoDBRepository($client, 'tableName');

        // Shop with null rotation fields
        $shop = new DynamoDBShop('shopId', 'shopUrl', 'shopSecret', 'clientId', 'clientSecret', true);

        $repository->updateShop($shop);
    }

    public function testGetShopWithEmptyTimestampReturnsNull(): void
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
                'clientId' => new AttributeValue(['S' => 'clientId']),
                'clientSecret' => new AttributeValue(['S' => 'clientSecret']),
                'active' => new AttributeValue(['BOOL' => true]),
                'secretsRotatedAt' => new AttributeValue(['S' => '']),
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');
        $shop = $repository->getShopFromId('shopId');

        static::assertNotNull($shop);
        // Empty timestamp string should result in null
        static::assertNull($shop->getSecretsRotatedAt());
        static::assertTrue($shop->isRegistrationConfirmed());
    }

    public function testGetShopBackwardCompatibilityWithoutRotationFields(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $output = $this->createMock(GetItemOutput::class);
        $output
            ->expects(static::once())
            ->method('getItem')
            ->willReturn([
                // Old data - only has original fields, no rotation fields
                'id' => new AttributeValue(['S' => 'shopId']),
                'url' => new AttributeValue(['S' => 'shopUrl']),
                'secret' => new AttributeValue(['S' => 'shopSecret']),
                'clientId' => new AttributeValue(['S' => 'clientId']),
                'clientSecret' => new AttributeValue(['S' => 'clientSecret']),
                'active' => new AttributeValue(['BOOL' => true]),
                // pendingShopSecret, pendingShopUrl, previousShopSecret, secretsRotatedAt are NOT present
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');
        $shop = $repository->getShopFromId('shopId');

        // Should not crash when reading old data without rotation fields
        static::assertNotNull($shop);
        static::assertSame('shopId', $shop->getShopId());
        static::assertSame('shopUrl', $shop->getShopUrl());
        static::assertSame('shopSecret', $shop->getShopSecret());

        // All rotation fields should default to null when not present
        static::assertNull($shop->getPendingShopSecret());
        static::assertNull($shop->getPendingShopUrl());
        static::assertNull($shop->getPreviousShopSecret());
        static::assertNull($shop->getSecretsRotatedAt());
        static::assertTrue($shop->isRegistrationConfirmed());
    }

    public function testGetShopWithoutHasVerifiedFlag(): void
    {
        $client = $this->createMock(DynamoDbClient::class);

        $output = $this->createMock(GetItemOutput::class);
        $output
            ->expects(static::once())
            ->method('getItem')
            ->willReturn([
                // Old data - only has original fields, no rotation fields
                'id' => new AttributeValue(['S' => 'shopId']),
                'url' => new AttributeValue(['S' => 'shopUrl']),
                'secret' => new AttributeValue(['S' => 'shopSecret']),
                'clientId' => new AttributeValue(['S' => 'clientId']),
                'clientSecret' => new AttributeValue(['S' => 'clientSecret']),
                'active' => new AttributeValue(['BOOL' => true]),
                // hasVerifiedWithDoubleSignature is NOT present
            ]);

        $client
            ->expects(static::once())
            ->method('getItem')
            ->willReturn($output);

        $repository = new DynamoDBRepository($client, 'tableName');
        $shop = $repository->getShopFromId('shopId');

        static::assertNotNull($shop);
        static::assertFalse($shop->hasVerifiedWithDoubleSignature());
        static::assertTrue($shop->isRegistrationConfirmed());
    }
}
