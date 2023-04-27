<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Authentication;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(RequestVerifier::class)]
#[CoversClass(SignatureNotFoundException::class)]
#[CoversClass(SignatureInvalidException::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(AppConfiguration::class)]
class RequestVerifierTest extends TestCase
{
    public function testAuthenticateRegistrationRequestMissingHeader(): void
    {
        $request = new Request('GET', 'https://my-app.com/register');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationRequest($request, new AppConfiguration('My App', 'my-secret'));
    }

    public function testAuthenticateRegistrationRequestMissingParameters(): void
    {
        $request = new Request('GET', 'https://my-app.com/register');
        $request = $request->withHeader('shopware-app-signature', 'invalid-signature');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationRequest($request, new AppConfiguration('My App', 'my-secret'));
    }

    public function testAuthenticateRegistrationRequestInvalidSignature(): void
    {
        $request = new Request('GET', 'https://my-app.com/register?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');
        $request = $request->withHeader('shopware-app-signature', 'invalid-signature');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationRequest($request, new AppConfiguration('My App', 'my-secret'));
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationRequestValid(): void
    {
        $request = new Request('GET', 'https://my-app.com/register?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');
        $request = $request->withHeader('shopware-app-signature', '96c91f86c822e11444b7a57b54ef125ed86b1a639c5360d45c5397daa8c3f70b');

        $verifier = new RequestVerifier();
        $verifier->authenticateRegistrationRequest($request, new AppConfiguration('My App', 'my-secret'));
    }

    public function testAuthenticatePostMissingHeader(): void
    {
        $request = new Request('POST', 'https://my-shop.com/webhook', [], 'body');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticatePostRequest($request, new MockShop('1', 'a', 'secret'));
    }

    public function testAuthenticatePostInvalidSignature(): void
    {
        $request = new Request('POST', 'https://my-shop.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'fake');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, new MockShop('1', 'a', 'secret'));
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticatePostValid(): void
    {
        $request = new Request('POST', 'https://my-shop.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'dc46983557fea127b43af721467eb9b3fde2338fe3e14f51952aa8478c13d355');

        $verifier = new RequestVerifier();
        $verifier->authenticatePostRequest($request, new MockShop('1', 'a', 'secret'));
    }

    public function testAuthenticateGetMissingSignature(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateGetRequest($request, new MockShop('1', 'a', 'secret'));
    }

    public function testAuthenticateGetInvalidSignature(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=fake');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticateGetRequest($request, new MockShop('1', 'a', 'secret'));
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateGetValid(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=9dd645162c4599f510a88a716a3aac9934c46d2964811e3efcdc53b4b672fe1c');

        $verifier = new RequestVerifier();
        $verifier->authenticateGetRequest($request, new MockShop('1', 'a', 'secret'));
    }
}
