<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class RefundResponse
{
    public static function open(): ResponseInterface
    {
        return self::createStatusResponse('open');
    }

    public static function inProgress(): ResponseInterface
    {
        return self::createStatusResponse('in_progress');
    }

    public static function cancelled(): ResponseInterface
    {
        return self::createStatusResponse('cancelled');
    }

    public static function failed(): ResponseInterface
    {
        return self::createStatusResponse('failed');
    }

    public static function completed(): ResponseInterface
    {
        return self::createStatusResponse('completed');
    }

    private static function createStatusResponse(string $status): ResponseInterface
    {
        return self::createResponse(array_filter(['status' => $status, 'message' => '']));
    }

    /**
     * @param array<mixed> $data
     */
    private static function createResponse(array $data): ResponseInterface
    {
        $psr = new Psr17Factory();

        return $psr->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
    }
}
