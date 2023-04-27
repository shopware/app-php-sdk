<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Authentication;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\AppConfiguration;

class RequestVerifier
{
    private const SHOPWARE_SHOP_SIGNATURE_HEADER = 'shopware-shop-signature';

    private const SHOPWARE_APP_SIGNATURE_HEADER = 'shopware-app-signature';

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateRegistrationRequest(RequestInterface $request, AppConfiguration $appConfiguration): void
    {
        $signature = $this->getSignatureFromHeader($request, self::SHOPWARE_APP_SIGNATURE_HEADER);

        parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries['shop-id'], $queries['shop-url'], $queries['timestamp'])) {
            throw new SignatureNotFoundException($request);
        }

        /** @var array{shop-id: string, shop-url: string, timestamp: string} $check */
        $check = $queries;

        $this->verifySignature(
            $request,
            $appConfiguration->getAppSecret(),
            $this->buildValidationQuery($check),
            $signature
        );
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticatePostRequest(RequestInterface $request, ShopInterface $shop): void
    {
        $signature = $this->getSignatureFromHeader($request, self::SHOPWARE_SHOP_SIGNATURE_HEADER);

        $content = $request->getBody()->getContents();
        $request->getBody()->rewind();
        $this->verifySignature(
            $request,
            $shop->getShopSecret(),
            $content,
            $signature
        );
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateGetRequest(RequestInterface $request, ShopInterface $shop): void
    {
        $signature = $this->getSignatureFromQuery($request);

        $this->verifySignature(
            $request,
            $shop->getShopSecret(),
            $this->removeSignatureFromQuery($request->getUri()->getQuery(), $signature),
            $signature
        );
    }

    /**
     * @throws SignatureNotFoundException
     */
    private function getSignatureFromQuery(RequestInterface $request): string
    {
        parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries[self::SHOPWARE_SHOP_SIGNATURE_HEADER])) {
            throw new SignatureNotFoundException($request);
        }

        $header = $queries[self::SHOPWARE_SHOP_SIGNATURE_HEADER];

        if (!is_string($header)) {
            throw new SignatureNotFoundException($request);
        }

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
        string $secret,
        string $message,
        string $signature
    ): void {
        $hmac = hash_hmac('sha256', $message, $secret);

        if (!hash_equals($hmac, $signature)) {
            throw new SignatureInvalidException($request);
        }
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
        return (string) preg_replace(
            sprintf('/&%s=%s/', self::SHOPWARE_SHOP_SIGNATURE_HEADER, $signature),
            '',
            $query
        );
    }
}
