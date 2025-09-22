<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Authentication;

use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\AppConfiguration;

class ResponseSigner
{
    /**
     * @param array{shop-id: string, shop-url: string} $proofParameters
     */
    public function getRegistrationSignature(AppConfiguration $appConfiguration, array $proofParameters): string
    {
        return $this->sign(
            implode('', [
                $proofParameters['shop-id'],
                $proofParameters['shop-url'],
                $appConfiguration->getAppName()
            ]),
            $appConfiguration->getAppSecret()
        );
    }

    public function signResponse(ResponseInterface $response, ShopInterface $shop): ResponseInterface
    {
        $content = $response->getBody()->getContents();
        $response->getBody()->rewind();

        return $response->withHeader(
            'shopware-app-signature',
            $this->sign($content, $shop->getShopSecret())
        );
    }

    private function sign(string $message, #[\SensitiveParameter] string $secret): string
    {
        return hash_hmac('sha256', $message, $secret);
    }
}
