<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient;

use Http\Discovery\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\HttpClient\Exception\AuthenticationFailedException;
use Shopware\App\SDK\Shop\ShopInterface;

class AuthenticatedClient implements ClientInterface
{
    /**
     * Grace period in seconds before the token expires.
     */
    private const TOKEN_EXPIRE_DIFF = 30;

    public function __construct(private readonly ClientInterface $client, private readonly ShopInterface $shop, private readonly CacheInterface $cache)
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request->withHeader('Authorization', 'Bearer ' . $this->fetchAccessToken()));
    }

    private function fetchAccessToken(): string
    {
        $cacheKey = $this->shop->getShopId() . '-access-token';

        $value = $this->cache->get($cacheKey);
        if (is_string($value)) {
            return $value;
        }

        $response = $this->client->sendRequest($this->createTokenRequest($this->shop));

        if ($response->getStatusCode() !== 200) {
            throw new AuthenticationFailedException($this->shop->getShopId(), $response);
        }

        /** @var array{access_token: string, expires_in: int} $token */
        $token = json_decode($response->getBody()->getContents(), true);

        $this->cache->set($cacheKey, $token['access_token'], $token['expires_in'] - self::TOKEN_EXPIRE_DIFF);

        return $token['access_token'];
    }

    /**
     * @throws \JsonException
     */
    private function createTokenRequest(ShopInterface $shop): RequestInterface
    {
        $factory = new Psr17Factory();
        $request = $factory->createRequest('POST', sprintf('%s/api/oauth/token', $shop->getShopUrl()));

        return $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($factory->createStream(json_encode([
                'grant_type' => 'client_credentials',
                'client_id' => $shop->getClientId(),
                'client_secret' => $shop->getClientSecret(),
            ], JSON_THROW_ON_ERROR)));
    }
}
