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

        $body = $response->getBody()->getContents();

        try {
            $token = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new AuthenticationFailedException($this->shop->getShopId(), $response);
        }

        if (!is_array($token) || !isset($token['access_token'], $token['expires_in'])) {
            throw new AuthenticationFailedException(
                $this->shop->getShopId(),
                $response,
            );
        }

        $this->cache->set($cacheKey, $token['access_token'], $token['expires_in'] - self::TOKEN_EXPIRE_DIFF);

        return (string) $token['access_token'];
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
                'client_id' => $shop->getShopClientId(),
                'client_secret' => $shop->getShopClientSecret(),
            ], JSON_THROW_ON_ERROR)));
    }
}
