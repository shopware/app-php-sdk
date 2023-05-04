<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class ActionButtonResponse
{
    /**
     * Opens in the Administration a new tab to the given URL and adds the shop-id as query parameter with the signature
     */
    public static function openNewTab(string $url): ResponseInterface
    {
        return self::createResponse([
            'actionType' => 'openNewTab',
            'payload' => [
                'redirectUrl' => $url
            ]
        ]);
    }

    /**
     * Reloads the Administration tab
     */
    public static function reload(): ResponseInterface
    {
        return self::createResponse([
            'actionType' => 'reload',
            'payload' => []
        ]);
    }

    /**
     * @param 'small'|'medium'|'large'|'fullscreen' $size
     * @param bool $expand - If true, the modal will be expanded to the full height of the screen
     */
    public static function modal(string $url, string $size = 'medium', bool $expand = false): ResponseInterface
    {
        return self::createResponse([
            'actionType' => 'openModal',
            'payload' => [
                'iframeUrl' => $url,
                'size' => $size,
                'expand' => $expand
            ]
        ]);
    }

    /**
     * @param 'success'|'error'|'info'|'warning' $type
     */
    public static function notification(string $type, string $message): ResponseInterface
    {
        return self::createResponse([
            'actionType' => 'notification',
            'payload' => [
                'message' => $message,
                'status' => $type
            ]
        ]);
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
