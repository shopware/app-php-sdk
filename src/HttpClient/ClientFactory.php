<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient;

use Http\Discovery\Psr18Client;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\HttpClient\SimpleHttpClient\SimpleHttpClient;
use Shopware\App\SDK\Shop\ShopInterface;

class ClientFactory
{
    public function __construct(
        private readonly CacheInterface $cache = new NullCache(),
        private readonly ClientInterface $client = new Psr18Client(),
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function createClient(ShopInterface $shop): ClientInterface
    {
        return new AuthenticatedClient(new LoggerClient($this->client, $this->logger), $shop, $this->cache);
    }

    public function createSimpleClient(ShopInterface $shop): SimpleHttpClient
    {
        return new SimpleHttpClient($this->createClient($shop));
    }
}
