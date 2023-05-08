<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

use DateTimeImmutable;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Context\ActionButton\ActionButton;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Module\Module;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentCaptureAction;
use Shopware\App\SDK\Context\Payment\PaymentFinalizeAction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Context\Payment\PaymentValidateAction;
use Shopware\App\SDK\Context\Payment\Refund;
use Shopware\App\SDK\Context\Payment\RefundAction;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Context\TaxProvider\TaxProvider;
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
            new DateTimeImmutable('@' . $body['timestamp'])
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

    public function assembleTaxProvider(RequestInterface $request, ShopInterface $shop): TaxProvider
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new TaxProvider(
            $shop,
            $this->parseSource($body['source']),
            new SalesChannelContext($body['context']),
            new Cart($body['cart'])
        );
    }

    public function assemblePaymentPay(RequestInterface $request, ShopInterface $shop): PaymentPayAction
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new PaymentPayAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new OrderTransaction($body['orderTransaction']),
            $body['returnUrl'] ?? null,
            $body['requestData'] ?? []
        );
    }

    public function assemblePaymentFinalize(RequestInterface $request, ShopInterface $shop): PaymentFinalizeAction
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new PaymentFinalizeAction(
            $shop,
            $this->parseSource($body['source']),
            new OrderTransaction($body['orderTransaction']),
            $body['queryParameters'] ?? []
        );
    }

    public function assemblePaymentCapture(RequestInterface $request, ShopInterface $shop): PaymentCaptureAction
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new PaymentCaptureAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new OrderTransaction($body['orderTransaction']),
            $body['preOrderPayment'] ?? []
        );
    }

    public function assemblePaymentValidate(RequestInterface $request, ShopInterface $shop): PaymentValidateAction
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new PaymentValidateAction(
            $shop,
            $this->parseSource($body['source']),
            new Cart($body['cart']),
            new SalesChannelContext($body['salesChannelContext']),
            $body['requestData'] ?? []
        );
    }

    public function assemblePaymentRefund(RequestInterface $request, ShopInterface $shop): RefundAction
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!is_array($body) || !isset($body['source']) || !is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }

        return new RefundAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new Refund($body['refund']),
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
