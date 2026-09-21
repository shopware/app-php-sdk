<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Framework;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Shopware\App\SDK\Framework\RequestBodyParser;

#[CoversClass(RequestBodyParser::class)]
class RequestBodyParserTest extends TestCase
{
    public function testDecodesTheBodyOfARequest(): void
    {
        $request = new Request('POST', 'https://example.com', [], '{"foo": "bar"}');

        static::assertSame(['foo' => 'bar'], RequestBodyParser::parse($request));
    }

    public function testLeavesTheBodyReadableForTheNextReader(): void
    {
        $request = new Request('POST', 'https://example.com', [], '{"foo": "bar"}');

        RequestBodyParser::parse($request);

        static::assertSame('{"foo": "bar"}', $request->getBody()->getContents());
    }

    public function testThrowsOnAnInvalidBody(): void
    {
        $request = new Request('POST', 'https://example.com', [], 'not-json');

        static::expectException(\JsonException::class);
        RequestBodyParser::parse($request);
    }

    public function testReturnsNullForABodyThatIsNotAJsonObject(): void
    {
        $request = new Request('POST', 'https://example.com', [], '"foo"');

        static::assertNull(RequestBodyParser::parse($request));
    }

    public function testReusesTheParsedBodyOfAServerRequest(): void
    {
        $request = static::createMock(ServerRequestInterface::class);
        $request->expects(static::once())->method('getParsedBody')->willReturn(['foo' => 'bar']);
        $request->expects(static::never())->method('getBody');

        static::assertSame(['foo' => 'bar'], RequestBodyParser::parse($request));
    }

    public function testFallsBackToTheBodyWhenAServerRequestWasNotParsed(): void
    {
        $request = new ServerRequest('POST', 'https://example.com', [], '{"foo": "bar"}');

        static::assertSame(['foo' => 'bar'], RequestBodyParser::parse($request));
    }

    public function testFallsBackToTheBodyWhenTheParsedBodyIsNotAnArray(): void
    {
        $request = (new ServerRequest('POST', 'https://example.com', [], '{"foo": "bar"}'))
            ->withParsedBody(new \stdClass());

        static::assertSame(['foo' => 'bar'], RequestBodyParser::parse($request));
    }
}
