<?php declare(strict_types=1);

namespace Shopware\AppSDK\Authentication;

use Psr\Http\Message\ResponseInterface;
use Shopware\AppSDK\Shop\ShopInterface;
use Shopware\AppSDK\AppConfiguration;

class ResponseSigner
{
    public function __construct(
        private readonly AppConfiguration $appConfiguration
    ) {
    }

    public function getRegistrationSignature(ShopInterface $shop): string
    {
        return $this->sign($shop->getShopId() . $shop->getShopUrl() . $this->appConfiguration->getAppName(), $this->appConfiguration->getAppSecret());
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

    private function sign(string $message, string $secret): string
    {
        return hash_hmac('sha256', $message, $secret);
    }
}
