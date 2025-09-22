<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Authentication;

use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Psr\Clock\ClockInterface;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Exception\SignatureInvalidException;

class RequestVerifier
{
    public const SHOPWARE_SHOP_SIGNATURE_HEADER = 'shopware-shop-signature';

    private const SHOPWARE_APP_SIGNATURE_HEADER = 'shopware-app-signature';

    public function __construct(
        private readonly ClockInterface  $clock = new SystemClock(new \DateTimeZone('UTC')),
    ) {
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateRegistrationRequest(
        RequestInterface $request,
        string $appSecret
    ): void {
        $signature = $this->getSignatureFromHeader($request, self::SHOPWARE_APP_SIGNATURE_HEADER);

        $this->verifySignature(
            $request,
            $appSecret,
            $this->getRegistrationValidationQuery($request),
            $signature
        );
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateRegistrationRequestWithShopSignature(
        RequestInterface $request,
        string $shopSecret
    ): void {
        $shopSignature = $this->getSignatureFromHeader($request, self::SHOPWARE_SHOP_SIGNATURE_HEADER);

        $this->verifySignature(
            $request,
            $shopSecret,
            $this->getRegistrationValidationQuery($request),
            $shopSignature
        );
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticatePostRequest(RequestInterface $request, string $secret, string $headerName = self::SHOPWARE_SHOP_SIGNATURE_HEADER): void
    {
        $signature = $this->getSignatureFromHeader($request, $headerName);

        $content = $request->getBody()->getContents();
        $request->getBody()->rewind();

        $this->verifySignature(
            $request,
            $secret,
            $content,
            $signature
        );
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateGetRequest(RequestInterface $request, string $secret): void
    {
        $signature = $this->getSignatureFromQuery($request);

        $this->verifySignature(
            $request,
            $secret,
            $this->removeSignatureFromQuery($request->getUri()->getQuery(), $signature),
            $signature
        );
    }

    public function authenticateStorefrontRequest(RequestInterface $request, string $shopId, string $secret): void
    {
        $token = $request->getHeaderLine('shopware-app-token');

        if ($token === '') {
            throw new SignatureNotFoundException($request);
        }

        if ($secret === '' || $shopId === '') {
            throw new ShopNotFoundException($shopId);
        }

        $key = InMemory::plainText($secret);

        (new JwtFacade())->parse(
            $token,
            new SignedWith(new Sha256(), $key),
            new StrictValidAt($this->clock),
            new IssuedBy($shopId)
        );
    }

    /**
     * @throws SignatureNotFoundException
     */
    private function getSignatureFromQuery(RequestInterface $request): string
    {
        \parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries[self::SHOPWARE_SHOP_SIGNATURE_HEADER])) {
            throw new SignatureNotFoundException($request);
        }

        /** @var string $header */
        $header = $queries[self::SHOPWARE_SHOP_SIGNATURE_HEADER];

        return $header;
    }

    /**
     * @throws SignatureNotFoundException
     */
    private function getSignatureFromHeader(RequestInterface $request, string $headerName): string
    {
        $signatureHeader = $request->getHeader($headerName);

        if (empty($signatureHeader)) {
            throw new SignatureNotFoundException($request);
        }

        return $signatureHeader[0];
    }

    /**
     * @throws SignatureInvalidException
     */
    private function verifySignature(
        RequestInterface $request,
        #[\SensitiveParameter]
        string           $secret,
        string           $message,
        string           $signature
    ): void {
        $hmac = hash_hmac('sha256', $message, $secret);

        if (!hash_equals($hmac, $signature)) {
            throw new SignatureInvalidException($request);
        }
    }

    /**
     * @throws SignatureNotFoundException
     */
    private function getRegistrationValidationQuery(RequestInterface $request): string
    {
        parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries['shop-id'], $queries['shop-url'], $queries['timestamp'])) {
            throw new SignatureNotFoundException($request);
        }

        /** @var array{shop-id: string, shop-url: string, timestamp: string} $check */
        $check = $queries;

        return $this->buildValidationQuery($check);
    }

    /**
     * @param array{shop-id: string, shop-url: string, timestamp: string} $queries
     */
    private function buildValidationQuery(array $queries): string
    {
        return sprintf(
            'shop-id=%s&shop-url=%s&timestamp=%s',
            $queries['shop-id'],
            $queries['shop-url'],
            $queries['timestamp']
        );
    }

    private function removeSignatureFromQuery(string $query, string $signature): string
    {
        /** @var string $query */
        $query = \preg_replace(
            sprintf('/&%s=%s/', self::SHOPWARE_SHOP_SIGNATURE_HEADER, $signature),
            '',
            $query
        );

        return $query;
    }
}
