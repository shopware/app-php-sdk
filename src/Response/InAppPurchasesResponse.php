<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class InAppPurchasesResponse
{
    /**
     * @param array<string> $purchases
     * @throws \JsonException
     */
    public static function filter(array $purchases): ResponseInterface
    {
        return self::createResponse(['purchases' => $purchases]);
    }

    /**
     * @param array<mixed> $data
     * @throws \JsonException
     */
    private static function createResponse(array $data): ResponseInterface
    {
        $psr = new Psr17Factory();

        return $psr->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
    }
}
