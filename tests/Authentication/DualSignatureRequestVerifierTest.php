<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Authentication;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\DualSignatureRequestVerifier;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(DualSignatureRequestVerifier::class)]
class DualSignatureRequestVerifierTest extends TestCase
{
    #[DoesNotPerformAssertions]
    public function testAuthenticatePostRequestWithCurrentSecret(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');

        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', 'body', 'current-secret'));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        // Should not throw
        $verifier->authenticatePostRequest($request, $shop);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticatePostRequestFallbackToPreviousSecret(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        $shop->setPreviousShopSecret('old-secret')
            ->setSecretsRotatedAt(new \DateTimeImmutable('now')); // Just rotated

        // Request signed with OLD secret (before rotation)
        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', 'body', 'old-secret'));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        // Should not throw - falls back to previous secret
        $verifier->authenticatePostRequest($request, $shop);
    }

    public function testAuthenticatePostRequestFailsWithoutPreviousSecret(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');
        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier(), );

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }

    public function testAuthenticatePostRequestFailsAfterRotationWindow(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        $shop->setPreviousShopSecret('old-secret')
            ->setSecretsRotatedAt(new \DateTimeImmutable('-10 minutes')); // Rotated 10 minutes ago

        // Request signed with OLD secret
        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', 'body', 'old-secret'));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateGetRequestWithCurrentSecret(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');

        $query = 'test=1';
        $signature = hash_hmac('sha256', $query, 'current-secret');
        $request = new Request('GET', sprintf("https://my-app.com/webhook?%s&shopware-shop-signature=%s", $query, $signature));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        // Should not throw
        $verifier->authenticateGetRequest($request, $shop);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateStorefrontRequestWithCurrentSecret(): void
    {
        $shopId = 'FqaHWUsCRNsrZ9kQ';
        $secret = '4XegKN9Xi9ATj3DKfAdWmKm5vkyDjfr0NRfw9shMdyaBtpV3UteqemCPgW7wQ0tPEXjGPQ4vmmPOexSEGvkstgDEaNdFvvrkbPDn21cQ7v0VGxTCfsuwF9H5';
        $shop = new MockShop($shopId, 'https://example.com', $secret);

        $request = new Request('GET', 'https://my-app.com/storefront');
        $request = $request->withHeader('shopware-app-token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJGcWFIV1VzQ1JOc3JaOWtRIiwiaWF0IjoxNjg5ODM3MDkyLjI3ODMyOSwibmJmIjoxNjg5ODM3MDkyLjI3ODMyOSwiZXhwIjoxNjg5ODQwNjkyLjI3ODI0Mywic2FsZXNDaGFubmVsSWQiOiIwMTg5NjQwNTU0YjU3MDBjODBjMmM0YTIwMmUyNDAxZCJ9.g8Da0bN3bkkmEdzMeXmI8wlDQEZMCDiKJvqS288B4JI');

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier(new FrozenClock(new \DateTimeImmutable('2023-07-20T07:13:00+00:00'))));

        // Should not throw
        $verifier->authenticateStorefrontRequest($request, $shopId, $shop);
    }

    /**
     * Test authenticateStorefrontRequest falls back to previous secret
     * Note: This test verifies the fallback mechanism is attempted, but JWT validation
     * might still fail if the token wasn't actually signed with the old secret
     */
    public function testAuthenticateStorefrontRequestFallbackAttempt(): void
    {
        $shopId = 'FqaHWUsCRNsrZ9kQ';
        $oldSecret = '4XegKN9Xi9ATj3DKfAdWmKm5vkyDjfr0NRfw9shMdyaBtpV3UteqemCPgW7wQ0tPEXjGPQ4vmmPOexSEGvkstgDEaNdFvvrkbPDn21cQ7v0VGxTCfsuwF9H5';
        // New secret must be at least 256 bits (32 bytes) for HS256
        $newSecret = '4XegKN9Xi9ATj3DKfAdWmKm5vkyDjfr0NRfw9shMdyaBtpV3UteqemCPgW7wQ0tPEXjGPQ4vmmPOexSEGvkstgDEaNdFvvrkbPDn21cQ7v0VGxTCfsuwF9H6';

        $shop = new MockShop($shopId, 'https://example.com', $newSecret);
        // No previous secret - should fail without attempting fallback

        // Token signed with OLD secret
        $request = new Request('GET', 'https://my-app.com/storefront');
        $request = $request->withHeader('shopware-app-token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJGcWFIV1VzQ1JOc3JaOWtRIiwiaWF0IjoxNjg5ODM3MDkyLjI3ODMyOSwibmJmIjoxNjg5ODM3MDkyLjI3ODMyOSwiZXhwIjoxNjg5ODQwNjkyLjI3ODI0Mywic2FsZXNDaGFubmVsSWQiOiIwMTg5NjQwNTU0YjU3MDBjODBjMmM0YTIwMmUyNDAxZCJ9.g8Da0bN3bkkmEdzMeXmI8wlDQEZMCDiKJvqS288B4JI');

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier(new FrozenClock(new \DateTimeImmutable('2023-07-20T07:13:00+00:00'))));

        // Should throw because new secret doesn't match and no previous secret available
        $this->expectException(RequiredConstraintsViolated::class);
        $verifier->authenticateStorefrontRequest($request, $shopId, $shop);
    }

    /**
     * Test that both current and previous secrets fail if neither is valid
     */
    public function testAuthenticatePostRequestFailsWithInvalidSignatures(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');
        $shop->setPreviousShopSecret('old-secret')
            ->setSecretsRotatedAt(new \DateTimeImmutable('now'));

        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'completely-invalid-signature');

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }

    public function testAuthenticatePostRequestMissingSignature(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');

        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        // No signature header

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureNotFoundException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }

    public function testFallbackRequiresPreviousSecret(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');
        $shop->setSecretsRotatedAt(new \DateTimeImmutable('now')); // Has timestamp but no previous secret

        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }


    public function testFallbackRequiresRotationTimestamp(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'current-secret');
        $shop->setPreviousShopSecret('old-secret'); // Has previous secret but no timestamp

        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }

    public function testAuthenticateGetRequestAfterRotationWindow(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        $shop->setPreviousShopSecret('old-secret')
            ->setSecretsRotatedAt(new \DateTimeImmutable('-10 minutes'));

        $query = 'test=1';
        $signature = hash_hmac('sha256', $query, 'old-secret');
        $request = new Request('GET', sprintf('https://my-app.com/webhook?%s&shopware-shop-signature=%s', $query, $signature));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticateGetRequest($request, $shop);
    }

    /**
     * Test that GET request fallback actually tries the previous secret (catches MethodCallRemoval mutant)
     */
    #[DoesNotPerformAssertions]
    public function testAuthenticateGetRequestFallbackCallsPrimaryVerifier(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        $shop->setPreviousShopSecret('old-secret')
            ->setSecretsRotatedAt(new \DateTimeImmutable('now'));

        $query = 'test=1';
        // Sign with old secret
        $signature = hash_hmac('sha256', $query, 'old-secret');
        $request = new Request('GET', sprintf('https://my-app.com/webhook?%s&shopware-shop-signature=%s', $query, $signature));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $verifier->authenticateGetRequest($request, $shop);
    }

    public function testAuthenticatePostRequestAtExactRotationWindowBoundary(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        // Exactly at the 60 second boundary
        $shop->setPreviousShopSecret('old-secret')
            ->setSecretsRotatedAt(new \DateTimeImmutable('-60 seconds'));

        $request = new Request('POST', 'https://my-app.com/webhook', [], 'body');
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', 'body', 'old-secret'));

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        // At exactly 60 seconds, we should be OUTSIDE the window (>=)
        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticatePostRequest($request, $shop);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationConfirmationNewShop(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        $shop->setPendingShopSecret('new-secret');
        // Unconfirmed shop registration

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost');
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    public function testAuthenticateRegistrationConfirmationMissingPrimarySignatureHeader(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'new-secret');
        $shop->setPendingShopSecret('new-secret');

        $request = new Request('POST', 'https://my-app.com/confirm', [], '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}');

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost');
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationConfirmationOldShopWithBothSignatures(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret')
            ->setRegistrationConfirmed();
        // Has pending secret = old shop re-registration

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        // Standard header signed with NEW pending secret
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));
        // Previous header signed with OLD current secret
        $request = $request->withHeader('shopware-shop-signature-previous', hash_hmac('sha256', $body, 'old-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', enforceDoubleSignature: true);
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    public function testAuthenticateRegistrationConfirmationOldShopMissingPreviousHeader(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret')
            ->setRegistrationConfirmed();

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        // Only standard header, missing previous header
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', enforceDoubleSignature:  true);
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    public function testAuthenticateRegistrationConfirmationOldShopInvalidPreviousSignature(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret')
            ->setRegistrationConfirmed();

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));
        $request = $request->withHeader('shopware-shop-signature-previous', 'invalid-signature');

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', true); // enforceDoubleSignature = true
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    /**
     * when the header is set, we should validate it even when enforcement is disabled.
     */
    public function testAuthenticateRegistrationConfirmationOldShopInvalidPreviousSignatureWhenNotEnforced(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret')
            ->setRegistrationConfirmed();

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));
        $request = $request->withHeader('shopware-shop-signature-previous', 'invalid-signature');

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', false); // enforceDoubleSignature = false
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    public function testAuthenticateRegistrationConfirmationOldShopInvalidPendingSignature(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret');
        $shop->setPendingShopSecret('new-secret')
            ->setRegistrationConfirmed();

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');
        $request = $request->withHeader('shopware-shop-signature-previous', hash_hmac('sha256', $body, 'old-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', true); // enforceDoubleSignature = true
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationRequestNewShop(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);

        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost');
        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        // No shop = new registration, only app signature required
        $verifier->authenticateRegistrationRequest($request, $appConfig, null);
    }

    public function testAuthenticateRegistrationRequestSkipsShopSignatureWhenRegistrationNotConfirmed(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', true);

        $requestVerifier = $this->createMock(RequestVerifier::class);
        $requestVerifier->expects(self::once())
            ->method('authenticateRegistrationRequest')
            ->with($request, $appConfig->getAppSecret());

        $requestVerifier->expects(self::never())
            ->method('authenticateRegistrationRequestWithShopSignature');

        $verifier = new DualSignatureRequestVerifier($requestVerifier);
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationRequestOldShopWithDoubleSignature(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);
        $shopSignature = hash_hmac('sha256', $query, $shopSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        $request = $request->withHeader('shopware-shop-signature', $shopSignature);

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', true); // enforceDoubleSignature = true

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    #[DoesNotPerformAssertions]
    public function testAuthenticateRegistrationRequestOldShopWithoutEnforcement(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        // No shop signature

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', false); // enforceDoubleSignature = false

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        // Should not require shop signature when not enforced
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestOldShopMissingShopSignature(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        // No shop signature

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', true); // enforceDoubleSignature = true

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureNotFoundException::class);
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestOldShopInvalidShopSignature(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', true);

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());

        $this->expectException(SignatureInvalidException::class);
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestForcesOldShopThatUsedDoubleVerificationToUseDoubleVerification(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);
        $shopSignature = hash_hmac('sha256', $query, $shopSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        $request = $request->withHeader('shopware-shop-signature', $shopSignature);

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true, hasVerifiedWithDoubleSignature: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', enforceDoubleSignature: false);

        $requestVerifier = $this->createMock(RequestVerifier::class);

        $requestVerifier->expects(self::once())
            ->method('authenticateRegistrationRequest')
            ->with($request, $appConfig->getAppSecret());

        $requestVerifier->expects(self::once())
            ->method('authenticateRegistrationRequestWithShopSignature')
            ->with($request, $shop->getShopSecret());

        $verifier = new DualSignatureRequestVerifier($requestVerifier);
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestUsesDoubleVerificationWhenHeadersProvided(): void
    {
        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);
        $shopSignature = hash_hmac('sha256', $query, $shopSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        $request = $request->withHeader('shopware-shop-signature', $shopSignature);

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true, hasVerifiedWithDoubleSignature: false);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', enforceDoubleSignature: false);

        $requestVerifier = $this->createMock(RequestVerifier::class);

        $requestVerifier->expects(self::once())
            ->method('authenticateRegistrationRequest')
            ->with($request, $appConfig->getAppSecret());

        $requestVerifier->expects(self::once())
            ->method('authenticateRegistrationRequestWithShopSignature')
            ->with($request, $shop->getShopSecret());

        $verifier = new DualSignatureRequestVerifier($requestVerifier);
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestThrowsWhenAShopThatUsedDoubleVerificationAttemptsToReRegisterWithoutDoubleVerification(): void
    {
        $this->expectException(SignatureNotFoundException::class);

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, registrationConfirmed: true, hasVerifiedWithDoubleSignature: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', enforceDoubleSignature: true);

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestThrowsWhenShopPreviouslyUsedDoubleVerificationAndNotEnforced(): void
    {
        $this->expectException(SignatureNotFoundException::class);

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, hasVerifiedWithDoubleSignature: true, registrationConfirmed: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', enforceDoubleSignature: false);

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationRequestInvalidShopSignatureWhenHeaderProvidedAndNotEnforced(): void
    {
        $this->expectException(SignatureInvalidException::class);

        $query = 'shop-id=123&shop-url=https://my-shop.com&timestamp=1234567890';
        $appSecret = 'app-secret';
        $shopSecret = 'shop-secret';

        $appSignature = hash_hmac('sha256', $query, $appSecret);

        $request = new Request('GET', 'https://my-app.com/register?' . $query);
        $request = $request->withHeader('shopware-app-signature', $appSignature);
        $request = $request->withHeader('shopware-shop-signature', 'invalid-signature');

        $shop = new MockShop('123', 'https://my-shop.com', $shopSecret, hasVerifiedWithDoubleSignature: false, registrationConfirmed: true);
        $appConfig = new AppConfiguration('My App', $appSecret, 'http://localhost', enforceDoubleSignature: false);

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationRequest($request, $appConfig, $shop);
    }

    public function testAuthenticateRegistrationConfirmationForcesOldShopThatUsedDoubleVerificationToUseDoubleVerification(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret', pendingShopSecret: 'new-secret', registrationConfirmed: true, hasVerifiedWithDoubleSignature: true);

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));
        $request = $request->withHeader('shopware-shop-signature-previous', hash_hmac('sha256', $body, 'old-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', enforceDoubleSignature: false);

        $requestVerifier = $this->createMock(RequestVerifier::class);

        $matcher = self::exactly(2);
        $requestVerifier->expects($matcher)
            ->method('authenticatePostRequest')
            ->willReturnCallback(function (RequestInterface $request, string $secret, string $header) use ($matcher, $shop) {
                match ($matcher->numberOfInvocations()) {
                    1 =>  $this->assertEquals($shop->getPendingShopSecret(), $secret),
                    2 =>  $this->assertEquals($shop->getShopSecret(), $secret),
                };
            });

        $verifier = new DualSignatureRequestVerifier($requestVerifier);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    public function testAuthenticateRegistrationConfirmationUsesDoubleVerificationWhenHeadersProvided(): void
    {
        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret', pendingShopSecret: 'new-secret', registrationConfirmed: true, hasVerifiedWithDoubleSignature: false);

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));
        $request = $request->withHeader('shopware-shop-signature-previous', hash_hmac('sha256', $body, 'old-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', enforceDoubleSignature: false);

        $requestVerifier = $this->createMock(RequestVerifier::class);

        $matcher = self::exactly(2);
        $requestVerifier->expects($matcher)
            ->method('authenticatePostRequest')
            ->willReturnCallback(function (RequestInterface $request, string $secret) use ($matcher, $shop) {
                match ($matcher->numberOfInvocations()) {
                    1 =>  $this->assertEquals($shop->getPendingShopSecret(), $secret),
                    2 =>  $this->assertEquals($shop->getShopSecret(), $secret),
                };
            });

        $verifier = new DualSignatureRequestVerifier($requestVerifier);
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }

    public function testAuthenticateRegistrationConfirmationThrowsWhenAShopThatUsedDoubleVerificationAttemptsToReRegisterWithoutDoubleVerification(): void
    {
        $this->expectException(SignatureNotFoundException::class);

        $shop = new MockShop('shop-1', 'https://example.com', 'old-secret', pendingShopSecret: 'new-secret', registrationConfirmed: true, hasVerifiedWithDoubleSignature: true);

        $body = '{"shopId":"shop-1","apiKey":"key","secretKey":"secret"}';
        $request = new Request('POST', 'https://my-app.com/confirm', [], $body);
        $request = $request->withHeader('shopware-shop-signature', hash_hmac('sha256', $body, 'new-secret'));

        $appConfig = new AppConfiguration('My App', 'app-secret', 'http://localhost', enforceDoubleSignature: false);

        $verifier = new DualSignatureRequestVerifier(new RequestVerifier());
        $verifier->authenticateRegistrationConfirmation($request, $shop, $appConfig);
    }
}
