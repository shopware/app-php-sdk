<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Shop;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Exception\SignatureInvalidException;

/**
 * Resolve and verify a request to a shop
 */
class ShopResolver
{
    public function __construct(private readonly ShopRepositoryInterface $shopRepository, private readonly RequestVerifier $requestVerifier = new RequestVerifier())
    {
    }

    public function resolveShop(RequestInterface $request): ShopInterface
    {
        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            return $this->resolveFromSource($request);
        }

        return $this->resolveFromQueryString($request);
    }

    /**
     * @throws ShopNotFoundException
     * @throws SignatureInvalidException
     * @throws \JsonException
     */
    private function resolveFromSource(RequestInterface $request): ShopInterface
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !isset($body['source']['shopId']) || !is_string($body['source']['shopId'])) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($body['source']['shopId']);

        if ($shop === null) {
            throw new ShopNotFoundException($body['source']['shopId']);
        }

        $this->requestVerifier->authenticatePostRequest($request, $shop);

        return $shop;
    }

    /**
     * @throws SignatureInvalidException
     * @throws MissingShopParameterException
     */
    private function resolveFromQueryString(RequestInterface $request): ShopInterface
    {
        parse_str($request->getUri()->getQuery(), $query);

        if (!isset($query['shop-id']) || !is_string($query['shop-id'])) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($query['shop-id']);

        if ($shop === null) {
            throw new ShopNotFoundException($query['shop-id']);
        }

        $this->requestVerifier->authenticateGetRequest($request, $shop);

        return $shop;
    }
}
