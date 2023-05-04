<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Response\ActionButtonResponse;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActionButtonResponse::class)]
class ActionButtonResponseTest extends TestCase
{
    public function testModal(): void
    {
        $response = ActionButtonResponse::modal('https://example.com');

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"actionType":"openModal","payload":{"iframeUrl":"https:\/\/example.com","size":"medium","expand":false}}', $response->getBody()->getContents());
    }

    public function testReload(): void
    {
        $response = ActionButtonResponse::reload();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"actionType":"reload","payload":[]}', $response->getBody()->getContents());
    }

    public function testOpenNewTab(): void
    {
        $response = ActionButtonResponse::openNewTab('foo.de');

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"actionType":"openNewTab","payload":{"redirectUrl":"foo.de"}}', $response->getBody()->getContents());
    }

    public function testNotification(): void
    {
        $response = ActionButtonResponse::notification('success', 'foo');

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"actionType":"notification","payload":{"message":"foo","status":"success"}}', $response->getBody()->getContents());
    }
}
