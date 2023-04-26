<?php

namespace Shopware\AppSDK\Tests\Authentication;

use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\AppSDK\AppConfiguration;
use Shopware\AppSDK\Authentication\ResponseSigner;
use Shopware\AppSDK\Test\MockShop;

#[CoversClass(ResponseSigner::class)]
#[CoversClass(AppConfiguration::class)]
#[CoversClass(MockShop::class)]
class ResponseSignerTest extends TestCase
{
    private ResponseSigner $signer;

    protected function setUp(): void
    {
        $this->signer = new ResponseSigner(new AppConfiguration('test', 'test', 'test.de'));
    }

    public function testRegisterSigning(): void
    {
        static::assertSame('a1a38c9580bb3bcd0df226086a9783ba4087671ce4968a4469e451c517fff445', $this->signer->getRegistrationSignature(new MockShop('test', 'test.de', 'test')));
    }

    public function testResponseSigning(): void
    {
        $response = new Response(200, [], 'test');

        $signedResponse = $this->signer->signResponse($response, new MockShop('test', 'test.de', 'test'));

        static::assertTrue($signedResponse->hasHeader('shopware-app-signature'));
        static::assertSame('88cd2108b5347d973cf39cdf9053d7dd42704876d8c9a9bd8e2d168259d3ddf7', $signedResponse->getHeaderLine('shopware-app-signature'));
    }
}
