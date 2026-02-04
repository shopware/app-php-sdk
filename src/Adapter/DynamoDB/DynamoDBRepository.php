<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Adapter\DynamoDB;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
use AsyncAws\DynamoDb\Input\UpdateItemInput;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;

/**
 * @implements ShopRepositoryInterface<DynamoDBShop>
 */
class DynamoDBRepository implements ShopRepositoryInterface
{
    public function __construct(private readonly DynamoDbClient $client, private readonly string $tableName)
    {
    }

    public function createShopStruct(string $shopId, string $shopUrl, string $shopSecret): ShopInterface
    {
        return new DynamoDBShop($shopId, $shopUrl, $shopSecret);
    }

    public function createShop(ShopInterface $shop): void
    {
        $item = [
            'id' => ['S' => $shop->getShopId()],
            'active' => ['BOOL' => $shop->isShopActive() ? '1' : '0'],
            'confirmed' => ['BOOL' => $shop->isRegistrationConfirmed() ? '1' : '0'],
            'url' => ['S' => $shop->getShopUrl()],
            'secret' => ['S' => $shop->getShopSecret()],
            'clientId' => ['S' => (string) $shop->getShopClientId()],
            'clientSecret' => ['S' => (string) $shop->getShopClientSecret()],
            'pendingShopSecret' => ['S' => (string) $shop->getPendingShopSecret()],
            'pendingShopUrl' => ['S' => (string) $shop->getPendingShopUrl()],
            'previousShopSecret' => ['S' => (string) $shop->getPreviousShopSecret()],
            'secretsRotatedAt' => ['S' => (string) ($shop->getSecretsRotatedAt()?->getTimestamp() ?? '')],
            'hasVerifiedWithDoubleSignature' => ['BOOL' => $shop->hasVerifiedWithDoubleSignature() ? '1' : '0'],
        ];

        $this->client->putItem(new PutItemInput([
            'TableName' => $this->tableName,
            'Item' => $item,
        ]));
    }

    public function getShopFromId(string $shopId): ShopInterface|null
    {
        $item = $this->client->getItem(new GetItemInput([
            'TableName' => $this->tableName,
            'Key' => [
                'id' => ['S' => $shopId],
            ],
        ]))->getItem();

        if (!$item) {
            return null;
        }

        $shopClientId = $item['clientId']->getS();
        $shopClientSecret = $item['clientSecret']->getS();

        if ($shopClientSecret === '') {
            $shopClientSecret = null;
        }

        if ($shopClientId === '') {
            $shopClientId = null;
        }

        $active = $item['active']->getBool();

        if ($active === null) {
            $active = false;
        }

        $confirmed = true;
        if (isset($item['confirmed'])) {
            $confirmed = $item['confirmed']->getBool();
            if ($confirmed === null) {
                $confirmed = false;
            }
        }

        $pendingShopSecret = isset($item['pendingShopSecret']) ? $item['pendingShopSecret']->getS() : null;
        $pendingShopUrl = isset($item['pendingShopUrl']) ? $item['pendingShopUrl']->getS() : null;
        $previousShopSecret = isset($item['previousShopSecret']) ? $item['previousShopSecret']->getS() : null;

        if ($pendingShopSecret === '') {
            $pendingShopSecret = null;
        }

        if ($pendingShopUrl === '') {
            $pendingShopUrl = null;
        }

        if ($previousShopSecret === '') {
            $previousShopSecret = null;
        }

        $secretsRotatedAt = null;

        if (isset($item['secretsRotatedAt'])) {
            $timestamp = $item['secretsRotatedAt']->getS();
            if ($timestamp !== null && $timestamp !== '') {
                $secretsRotatedAt = (new \DateTimeImmutable())->setTimestamp((int) $timestamp);
            }
        }

        $hasVerifiedWithDoubleSignature = false;
        if (isset($item['hasVerifiedWithDoubleSignature'])) {
            $hasVerifiedWithDoubleSignature = $item['hasVerifiedWithDoubleSignature']->getBool();

            if ($hasVerifiedWithDoubleSignature === null) {
                $hasVerifiedWithDoubleSignature = false;
            }
        }

        return new DynamoDBShop(
            $item['id']->getS() ?? '',
            $item['url']->getS() ?? '',
            $item['secret']->getS() ?? '',
            $shopClientId,
            $shopClientSecret,
            $active,
            $pendingShopSecret,
            $pendingShopUrl,
            $previousShopSecret,
            $secretsRotatedAt,
            $hasVerifiedWithDoubleSignature,
            $confirmed,
        );
    }

    public function updateShop(ShopInterface $shop): void
    {
        $this->client->updateItem(new UpdateItemInput([
            'TableName' => $this->tableName,
            'Key' => [
                'id' => ['S' => $shop->getShopId()],
            ],
            'UpdateExpression' => 'SET active = :active, confirmed = :confirmed, #u = :url, secret = :secret, clientId = :clientId, clientSecret = :clientSecret, pendingShopSecret = :pendingShopSecret, pendingShopUrl = :pendingShopUrl, previousShopSecret = :previousShopSecret, secretsRotatedAt = :secretsRotatedAt, hasVerifiedWithDoubleSignature = :hasVerifiedWithDoubleSignature',
            'ExpressionAttributeNames' => [
                '#u' => 'url',
            ],
            'ExpressionAttributeValues' => [
                ':active' => ['BOOL' => $shop->isShopActive() ? '1' : '0'],
                ':confirmed' => ['BOOL' => $shop->isRegistrationConfirmed() ? '1' : '0'],
                ':url' => ['S' => $shop->getShopUrl()],
                ':secret' => ['S' => $shop->getShopSecret()],
                ':clientId' => ['S' => (string) $shop->getShopClientId()],
                ':clientSecret' => ['S' => (string) $shop->getShopClientSecret()],
                ':pendingShopSecret' => ['S' => (string) $shop->getPendingShopSecret()],
                ':pendingShopUrl' => ['S' => (string) $shop->getPendingShopUrl()],
                ':previousShopSecret' => ['S' => (string) $shop->getPreviousShopSecret()],
                ':secretsRotatedAt' => ['S' => (string) ($shop->getSecretsRotatedAt()?->getTimestamp() ?? '')],
                ':hasVerifiedWithDoubleSignature' => ['BOOL' => $shop->hasVerifiedWithDoubleSignature() ? '1' : '0'],
            ],
        ]));
    }

    public function deleteShop(string $shopId): void
    {
        $this->client->deleteItem(new DeleteItemInput([
            'TableName' => $this->tableName,
            'Key' => [
                'id' => ['S' => $shopId],
            ],
        ]));
    }
}
