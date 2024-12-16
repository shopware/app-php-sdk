<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Framework\Collection;

class InAppPurchasesResponse
{
    /**
     * @param Collection<string> $purchases
     */
    public static function filter(Collection $purchases): ResponseInterface
    {
        return self::createResponse(['purchases' => $purchases->all()]);
    }

    /**
     * @param array<mixed> $data
     */
    private static function createResponse(array $data): ResponseInterface
    {
        $psr = new Psr17Factory();

        return $psr->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr->createStream(\json_encode($data, \JSON_THROW_ON_ERROR)));
    }
}
