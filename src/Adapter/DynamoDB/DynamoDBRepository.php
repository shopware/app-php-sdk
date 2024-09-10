<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Adapter\DynamoDB;

use AsyncAws\DynamoDb\DynamoDbClient;
use AsyncAws\DynamoDb\Input\DeleteItemInput;
use AsyncAws\DynamoDb\Input\GetItemInput;
use AsyncAws\DynamoDb\Input\PutItemInput;
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
        $this->client->putItem(new PutItemInput([
            'TableName' => $this->tableName,
            'Item' => [
                'id' => ['S' => $shop->getShopId()],
                'active' => ['BOOL' => $shop->isShopActive() ? '1' : '0'],
                'url' => ['S' => $shop->getShopUrl()],
                'secret' => ['S' => $shop->getShopSecret()],
                'clientId' => ['S' => (string) $shop->getShopClientId()],
                'clientSecret' => ['S' => (string) $shop->getShopClientSecret()],
            ],
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

        return new DynamoDBShop(
            $item['id']->getS() ?? '',
            $item['url']->getS() ?? '',
            $item['secret']->getS() ?? '',
            $shopClientId,
            $shopClientSecret,
            $active,
        );
    }

    public function updateShop(ShopInterface $shop): void
    {
        $this->createShop($shop);
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
