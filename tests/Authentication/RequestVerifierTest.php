<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Authentication;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;

#[CoversClass(RequestVerifier::class)]
class RequestVerifierTest extends TestCase
{
    public function testAuthenticateRegistrationRequestMissingHeader(): void
    {
        $request = new Request('GET', 'https://my-app.com/register');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationRequest($request, 'my-secret');
    }

    public function testAuthenticateRegistrationRequestMissingParameters(): void
    {
        $request = new Request('GET', 'https://my-app.com/register');
        $request = $request->withHeader('shopware-app-signature', 'invalid-signature');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationRequest($request, 'my-secret');
    }

    public function testAuthenticateRegistrationRequestInvalidSignature(): void
    {
        $request = new Request('GET', 'https://my-app.com/register?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');
        $request = $request->withHeader('shopware-app-signature', 'invalid-signature');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationRequest($request, 'my-secret');
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationRequestValid(): void
    {
        $request = new Request('GET', 'https://my-app.com/register?shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890');
        $request = $request->withHeader('shopware-app-signature', '96c91f86c822e11444b7a57b54ef125ed86b1a639c5360d45c5397daa8c3f70b');

        $verifier = new RequestVerifier();
        $verifier->authenticateRegistrationRequest($request, 'my-secret');
    }

    public function testAuthenticatePostMissingHeader(): void
    {
        $request = new Request('POST', 'https://my-shop.com/webhook', [], 'body');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticatePostRequest($request, 'secret');
    }

    public function testAuthenticatePostInvalidSignature(): void
    {
        $request = new Request('POST', 'https://my-shop.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'fake');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, 'secret');
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticatePostValid(): void
    {
        $request = new Request('POST', 'https://my-shop.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'dc46983557fea127b43af721467eb9b3fde2338fe3e14f51952aa8478c13d355');

        $verifier = new RequestVerifier();
        $verifier->authenticatePostRequest($request, 'secret');
    }

    public function testAuthenticatePostRequestRewindsBody(): void
    {
        $body = static::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('rewind');

        $body
            ->method('getContents')
            ->willReturn('body');

        $request = new Request('POST', 'https://my-shop.com/webhook?shopware-shop-signature=', [], $body);
        $request = $request->withHeader('shopware-shop-signature', 'dc46983557fea127b43af721467eb9b3fde2338fe3e14f51952aa8478c13d355');

        $verifier = new RequestVerifier();
        $verifier->authenticatePostRequest($request, 'secret');
    }

    public function testAuthenticateGetMissingSignature(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook');

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateGetRequest($request, 'secret');
    }

    public function testAuthenticateGetInvalidSignature(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=fake');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticateGetRequest($request, 'secret');
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateGetValid(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=9dd645162c4599f510a88a716a3aac9934c46d2964811e3efcdc53b4b672fe1c');

        $verifier = new RequestVerifier();
        $verifier->authenticateGetRequest($request, 'secret');
    }

    public function testStorefrontRequestEmpty(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=9dd645162c4599f510a88a716a3aac9934c46d2964811e3efcdc53b4b672fe1c');

        $verifier = new RequestVerifier();
        $this->expectException(SignatureNotFoundException::class);

        $verifier->authenticateStorefrontRequest($request, '1', 'secret');
    }

    public function testStorefrontRequestInvalidShop(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=9dd645162c4599f510a88a716a3aac9934c46d2964811e3efcdc53b4b672fe1c');
        $request = $request->withHeader('shopware-app-token', 'bla');

        $verifier = new RequestVerifier();

        $this->expectException(ShopNotFoundException::class);
        $verifier->authenticateStorefrontRequest($request, '1', '');
    }

    #[DoesNotPerformAssertions]
    public function testStorefrontRequest(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=9dd645162c4599f510a88a716a3aac9934c46d2964811e3efcdc53b4b672fe1c');
        $request = $request->withHeader('shopware-app-token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJGcWFIV1VzQ1JOc3JaOWtRIiwiaWF0IjoxNjg5ODM3MDkyLjI3ODMyOSwibmJmIjoxNjg5ODM3MDkyLjI3ODMyOSwiZXhwIjoxNjg5ODQwNjkyLjI3ODI0Mywic2FsZXNDaGFubmVsSWQiOiIwMTg5NjQwNTU0YjU3MDBjODBjMmM0YTIwMmUyNDAxZCJ9.g8Da0bN3bkkmEdzMeXmI8wlDQEZMCDiKJvqS288B4JI');
        $request = $request->withHeader('shopware-app-shop-id', 'FqaHWUsCRNsrZ9kQ');

        $verifier = new RequestVerifier(new FrozenClock(new DateTimeImmutable('2023-07-20T07:13:00+00:00')));

        $verifier->authenticateStorefrontRequest($request, 'FqaHWUsCRNsrZ9kQ', '4XegKN9Xi9ATj3DKfAdWmKm5vkyDjfr0NRfw9shMdyaBtpV3UteqemCPgW7wQ0tPEXjGPQ4vmmPOexSEGvkstgDEaNdFvvrkbPDn21cQ7v0VGxTCfsuwF9H5');
    }

    public function testStorefrontRequestInvalid(): void
    {
        $request = new Request('GET', 'https://my-shop.com/webhook?test=1&shopware-shop-signature=9dd645162c4599f510a88a716a3aac9934c46d2964811e3efcdc53b4b672fe1c');
        $request = $request->withHeader('shopware-app-token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJGcWFIV1VzQ1JOc3JaOWtRIiwiaWF0IjoxNjg5ODM3MDkyLjI3ODMyOSwibmJmIjoxNjg5ODM3MDkyLjI3ODMyOSwiZXhwIjoxNjg5ODQwNjkyLjI3ODI0Mywic2FsZXNDaGFubmVsSWQiOiIwMTg5NjQwNTU0YjU3MDBjODBjMmM0YTIwMmUyNDAxZCJ9.g8Da0bN3bkkmEdzMeXmI8wlDQEZMCDiKJvqS288B4JI');
        $request = $request->withHeader('shopware-app-shop-id', 'FqaHWUsCRNsrZ9kQ');

        $verifier = new RequestVerifier(new FrozenClock(new DateTimeImmutable('2023-07-20T07:13:00+00:00')));

        static::expectException(RequiredConstraintsViolated::class);
        $verifier->authenticateStorefrontRequest($request, 'FqaHWUsCRNsrZ9kQ', '1XegKN9Xi9ATj3DKfAdWmKm5vkyDjfr0NRfw9shMdyaBtpV3UteqemCPgW7wQ0tPEXjGPQ4vmmPOexSEGvkstgDEaNdFvvrkbPDn21cQ7v0VGxTCfsuwF9H5');
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationRequestWithShopSignature(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $shopSecret = 'shop-secret';

        // Calculate shop signature with the shop secret over the entire query
        $shopSignature = hash_hmac('sha256', $query, $shopSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-shop-signature', $shopSignature);

        $verifier = new RequestVerifier();
        $verifier->authenticateRegistrationRequestWithShopSignature($request, $shopSecret);
    }

    public function testAuthenticateRegistrationRequestWithShopSignatureMissing(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        // No shop signature header

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationRequestWithShopSignature($request, 'shop-secret');
    }

    public function testAuthenticateRegistrationRequestWithShopSignatureInvalid(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationRequestWithShopSignature($request, 'shop-secret');
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticatePostRequestWithCustomHeader(): void
    {
        $body = '{"test":"data"}';
        $secret = 'test-secret';
        $signature = hash_hmac('sha256', $body, $secret);

        $request = new Request('POST', 'https://my-shop.com/confirm', [], $body);
        $request = $request->withHeader('custom-header', $signature);

        $verifier = new RequestVerifier();
        $verifier->authenticatePostRequest($request, $secret, 'custom-header');
    }

    public function testAuthenticatePostRequestWithCustomHeaderMissing(): void
    {
        $request = new Request('POST', 'https://my-shop.com/confirm', [], '{"test":"data"}');
        // No custom header

        $verifier = new RequestVerifier();
        static::expectException(SignatureNotFoundException::class);
        $verifier->authenticatePostRequest($request, 'secret', 'custom-header');
    }

    public function testAuthenticatePostRequestWithCustomHeaderInvalid(): void
    {
        $request = new Request('POST', 'https://my-shop.com/confirm', [], '{"test":"data"}');
        $request = $request->withHeader('custom-header', 'invalid-signature');

        $verifier = new RequestVerifier();
        static::expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, 'secret', 'custom-header');
    }
}
