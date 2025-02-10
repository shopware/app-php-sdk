<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\InAppPurchase;

use Http\Client\Exception\RequestException;
use Nyholm\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\HttpClient\NullCache;
use Strobotti\JWK\KeySet;
use Strobotti\JWK\KeySetFactory;

/**
 * @internal
 *
 * Fetches a JWKS from the SBP and stores it locally by the system to reduce the number of requests to the SBP.
 * The key gets refreshed only when forced to do so manually.
 */
class SBPStoreKeyFetcher
{
    final public const SBP_JWT_API_HOST = 'https://api.shopware.com';
    final public const SBP_JWT_CACHE_KEY = 'store-jwks-key';

    public function __construct(
        private readonly ClientInterface $client,
        private readonly CacheInterface $cache = new NullCache(),
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function getKey(bool $refresh = false): KeySet
    {
        if ($refresh) {
            $this->fetchAndStoreKey();
        }

        return $this->getStoredKeys();
    }

    private function getStoredKeys(): KeySet
    {
        /** @var string|null $storedKeys */
        $storedKeys = $this->cache->get(self::SBP_JWT_CACHE_KEY);

        if (!$storedKeys) {
            return new KeySet();
        }

        return (new KeySetFactory())->createFromJSON($storedKeys);
    }

    private function fetchAndStoreKey(): void
    {
        $request = new Request('GET', \sprintf('%s/inappfeatures/jwks', self::SBP_JWT_API_HOST));

        try {
            $response = $this->client->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                throw new RequestException('Request to the SBP failed.', $request);
            }

            $result = $response->getBody()->getContents();

            if (!$this->cache->set(self::SBP_JWT_CACHE_KEY, $result)) {
                throw new \Exception('The JWKS was not stored in the cache successfully.');
            }
        } catch (\Throwable $e) {
            $this->logger->error('Unable to fetch a JWKS token from SBP: ' . $e->getMessage());
        }
    }
}
