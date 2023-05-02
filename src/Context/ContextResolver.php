<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Context\ActionButton\ActionButton;
use Shopware\App\SDK\Context\Module\Module;
use Shopware\App\SDK\Context\Webhook\Webhook;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;
use Shopware\App\SDK\Shop\ShopInterface;

class ContextResolver
{
    public function assembleWebhook(RequestInterface $request, ShopInterface $shop): Webhook
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new Webhook(
            $shop,
            $this->parseSource($body['source']),
            $body['data']['event'],
            $body['data']['payload'],
            new \DateTimeImmutable('@' . $body['timestamp'])
        );
    }

    public function assembleActionButton(RequestInterface $request, ShopInterface $shop): ActionButton
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new ActionButton(
            $shop,
            $this->parseSource($body['source']),
            $body['data']['ids'],
            $body['data']['entity'],
            $body['data']['action']
        );
    }

    public function assembleModule(RequestInterface $request, ShopInterface $shop): Module
    {
        parse_str($request->getUri()->getQuery(), $params);

        if (!isset($params['sw-version']) || !is_string($params['sw-version']) || !isset($params['sw-context-language']) || !is_string($params['sw-context-language']) || !isset($params['sw-user-language']) || !is_string($params['sw-user-language'])) {
            throw new MalformedWebhookBodyException();
        }

        return new Module(
            $shop,
            $params['sw-version'],
            $params['sw-context-language'],
            $params['sw-user-language']
        );
    }

    /**
     * @param array<string, mixed> $source
     * @return ActionSource
     */
    private function parseSource(array $source): ActionSource
    {
        if (!isset($source['url']) || !is_string($source['url']) || !isset($source['appVersion']) || !is_string($source['appVersion'])) {
            throw new MalformedWebhookBodyException();
        }

        return new ActionSource(
            $source['url'],
            $source['appVersion']
        );
    }
}
