<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class RefundResponse
{
    public const ACTION_CANCEL = 'cancel';
    public const ACTION_COMPLETE = 'complete';
    public const ACTION_FAIL = 'fail';
    public const ACTION_PROCESS = 'process';
    public const ACTION_REOPEN = 'reopen';

    public static function open(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_REOPEN);
    }

    public static function inProgress(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_PROCESS);
    }

    public static function cancelled(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_CANCEL);
    }

    public static function failed(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_FAIL);
    }

    public static function completed(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_COMPLETE);
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
