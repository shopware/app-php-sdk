<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;
use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

class GatewayResponse
{
    /**
     * @param Collection<CheckoutGatewayCommand> $checkoutCommands
     */
    public static function createCheckoutGatewayResponse(Collection $checkoutCommands): ResponseInterface
    {
        return self::createResponse($checkoutCommands->jsonSerialize());
    }

    /**
     * @param Collection<ContextGatewayCommand> $contextCommands
     */
    public static function createContextGatewayResponse(Collection $contextCommands): ResponseInterface
    {
        return self::createResponse($contextCommands->jsonSerialize());
    }

    /**
     * @param array<mixed> $data
     */
    private static function createResponse(array $data): ResponseInterface
    {
        $psr = new Psr17Factory();

        return $psr
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr->createStream(\json_encode($data, \JSON_THROW_ON_ERROR)));
    }
}
