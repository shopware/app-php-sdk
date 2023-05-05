<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Constraint\IsEqual;
use Psr\Http\Message\StreamInterface;
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
        $request = new Request('GET', 'https://example.com', ['X-FOO' => 'BAR']);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('info')
            ->with('Request: GET https://example.com', [
                'request' => [
                    'method' => 'GET',
                    'uri' => $request->getUri(),
                    'headers' => ['Host' => ['example.com'], 'X-FOO' => ['BAR']],
                ],
                'response' => [
                    'status' => 200,
                    'headers' => ['X-BAR' => ['FOO']],
                ]
            ]);

        $client = new LoggerClient(new MockClient([
            new Response(200, ['X-BAR' => 'FOO'], '{"foo": "bar"}')
        ]), $logger);

        $client->sendRequest($request);
    }

    public function testLoggerDebugIsWritten(): void
    {
        $logger = static::createMock(LoggerInterface::class);
        $logger
            ->expects(static::exactly(2))
            ->method('debug')
            ->willReturnCallback(function (string $message, array $context): void {
                static::assertArrayHasKey('body', $context);
                static::assertIsString($context['body']);

                static::assertThat($message, static::logicalOr(
                    new IsEqual('Request body'),
                    new IsEqual('Response body')
                ));

                $body = $context['body'];

                static::assertThat($body, static::logicalOr(
                    new IsEqual('foo=bar'),
                    new IsEqual('{"foo": "bar"}')
                ));
            });

        $client = new LoggerClient(new MockClient([
            new Response(200, [], '{"foo": "bar"}')
        ]), $logger);

        $client->sendRequest(new Request('GET', 'https://example.com', [], 'foo=bar'));
    }

    public function testBodyRewindIsCalled(): void
    {
        $request = new Request('GET', 'https://example.com');

        $body = static::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('rewind');

        $body
            ->method('getContents')
            ->willReturn('foo=bar');

        $request = $request->withBody($body);

        $client = new LoggerClient(new MockClient([
            new Response(200, [], '{"foo": "bar"}')
        ]), static::createMock(LoggerInterface::class));

        $client->sendRequest($request);
    }
}
