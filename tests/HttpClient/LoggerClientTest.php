<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Shopware\App\SDK\HttpClient\LoggerClient;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockClient;

#[CoversClass(LoggerClient::class)]
#[CoversClass(MockClient::class)]
class LoggerClientTest extends TestCase
{
    public function testRequestGetsLogged(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Request: GET https://example.com');

        $client = new LoggerClient(new MockClient([
            new Response(200, [], '{"foo": "bar"}')
        ]), $logger);

        $client->sendRequest(new Request('GET', 'https://example.com'));
    }
}
