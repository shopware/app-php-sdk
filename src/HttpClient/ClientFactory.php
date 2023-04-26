<?php

declare(strict_types=1);

namespace Shopware\App\SDK\HttpClient;

use Http\Discovery\Psr18Client;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\Shop\ShopInterface;

class ClientFactory
{
    public function __construct(private readonly CacheInterface $cache = new NullCache())
    {
    }

    public function createClient(ShopInterface $shop, ?ClientInterface $client = null): ClientInterface
    {
        return new AuthenticatedClient($client ?? new Psr18Client(), $shop, $this->cache);
    }
}
