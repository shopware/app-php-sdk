<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context;

use DateTimeImmutable;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Context\ActionButton\ActionButtonAction;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Gateway\Checkout\CheckoutGatewayAction;
use Shopware\App\SDK\Context\Gateway\InAppFeatures\FilterAction;
use Shopware\App\SDK\Context\Module\ModuleAction;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Payment\PaymentCaptureAction;
use Shopware\App\SDK\Context\Payment\PaymentFinalizeAction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Context\Payment\PaymentRecurringAction;
use Shopware\App\SDK\Context\Payment\PaymentValidateAction;
use Shopware\App\SDK\Context\Payment\RecurringData;
use Shopware\App\SDK\Context\Payment\Refund;
use Shopware\App\SDK\Context\Payment\RefundAction;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Context\Storefront\StorefrontAction;
use Shopware\App\SDK\Context\Storefront\StorefrontClaims;
use Shopware\App\SDK\Context\TaxProvider\TaxProviderAction;
use Shopware\App\SDK\Context\Webhook\WebhookAction;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

class ContextResolver
{
    /**
     * @throws JsonException
     */
    public function assembleWebhook(RequestInterface $request, ShopInterface $shop): WebhookAction
    {
        $body = $this->getRequestBody($request);

        return new WebhookAction(
            $shop,
            $this->parseSource($body['source']),
            $body['data']['event'],
            $body['data']['payload'],
            new DateTimeImmutable('@' . $body['timestamp'])
        );
    }

    /**
     * @throws JsonException
     */
    public function assembleActionButton(RequestInterface $request, ShopInterface $shop): ActionButtonAction
    {
        $body = $this->getRequestBody($request);

        return new ActionButtonAction(
            $shop,
            $this->parseSource($body['source']),
            $body['data']['ids'],
            $body['data']['entity'],
            $body['data']['action']
        );
    }

    public function assembleModule(RequestInterface $request, ShopInterface $shop): ModuleAction
    {
        parse_str($request->getUri()->getQuery(), $params);

        if (!isset($params['sw-version'], $params['sw-context-language']) || !is_string($params['sw-version']) || !is_string($params['sw-context-language']) || !isset($params['sw-user-language']) || !is_string($params['sw-user-language'])) {
            throw new MalformedWebhookBodyException();
        }

        return new ModuleAction(
            $shop,
            $params['sw-version'],
            $params['sw-context-language'],
            $params['sw-user-language']
        );
    }

    /**
     * @throws JsonException
     */
    public function assembleTaxProvider(RequestInterface $request, ShopInterface $shop): TaxProviderAction
    {
        $body = $this->getRequestBody($request);

        return new TaxProviderAction(
            $shop,
            $this->parseSource($body['source']),
            new SalesChannelContext($body['context']),
            new Cart($body['cart'])
        );
    }

    /**
     * @throws JsonException
     */
    public function assemblePaymentPay(RequestInterface $request, ShopInterface $shop): PaymentPayAction
    {
        $body = $this->getRequestBody($request);

        return new PaymentPayAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new OrderTransaction($body['orderTransaction']),
            $body['returnUrl'] ?? null,
            isset($body['recurring']) ? new RecurringData($body['recurring']) : null,
            $body['requestData'] ?? []
        );
    }

    /**
     * @throws JsonException
     */
    public function assemblePaymentFinalize(RequestInterface $request, ShopInterface $shop): PaymentFinalizeAction
    {
        $body = $this->getRequestBody($request);

        return new PaymentFinalizeAction(
            $shop,
            $this->parseSource($body['source']),
            new OrderTransaction($body['orderTransaction']),
            isset($body['recurring']) ? new RecurringData($body['recurring']) : null,
            $body['queryParameters'] ?? []
        );
    }

    /**
     * @throws JsonException
     */
    public function assemblePaymentCapture(RequestInterface $request, ShopInterface $shop): PaymentCaptureAction
    {
        $body = $this->getRequestBody($request);

        return new PaymentCaptureAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new OrderTransaction($body['orderTransaction']),
            isset($body['recurring']) ? new RecurringData($body['recurring']) : null,
            $body['preOrderPayment'] ?? []
        );
    }

    /**
     * @throws JsonException
     */
    public function assemblePaymentRecurringCapture(RequestInterface $request, ShopInterface $shop): PaymentRecurringAction
    {
        $body = $this->getRequestBody($request);

        return new PaymentRecurringAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new OrderTransaction($body['orderTransaction']),
        );
    }

    /**
     * @throws JsonException
     */
    public function assemblePaymentValidate(RequestInterface $request, ShopInterface $shop): PaymentValidateAction
    {
        $body = $this->getRequestBody($request);

        return new PaymentValidateAction(
            $shop,
            $this->parseSource($body['source']),
            new Cart($body['cart']),
            new SalesChannelContext($body['salesChannelContext']),
            $body['requestData'] ?? []
        );
    }

    /**
     * @throws JsonException
     */
    public function assemblePaymentRefund(RequestInterface $request, ShopInterface $shop): RefundAction
    {
        $body = $this->getRequestBody($request);

        return new RefundAction(
            $shop,
            $this->parseSource($body['source']),
            new Order($body['order']),
            new Refund($body['refund']),
        );
    }

    /**
     * @throws MalformedWebhookBodyException|JsonException
     */
    public function assembleStorefrontRequest(RequestInterface $request, ShopInterface $shop): StorefrontAction
    {
        $token = $request->getHeaderLine('shopware-app-token');

        if (empty($token)) {
            /** @infection-ignore-all false friend */
            throw new MalformedWebhookBodyException();
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new MalformedWebhookBodyException();
        }

        /** @var array<string, string> $claims */
        $claims = json_decode(base64_decode($parts[1]), true, flags: JSON_THROW_ON_ERROR);

        return new StorefrontAction(
            $shop,
            new StorefrontClaims($claims)
        );
    }

    /**
     * @throws JsonException
     */
    public function assembleCheckoutGatewayRequest(RequestInterface $request, ShopInterface $shop): CheckoutGatewayAction
    {
        $body = $this->getRequestBody($request);

        return new CheckoutGatewayAction(
            $shop,
            $this->parseSource($body['source']),
            new Cart($body['cart']),
            new SalesChannelContext($body['salesChannelContext']),
            new Collection(\array_flip($body['paymentMethods'])),
            new Collection(\array_flip($body['shippingMethods'])),
        );
    }

    /**
     * @throws JsonException
     */
    public function assembleInAppFeatureFilterRequest(RequestInterface $request, ShopInterface $shop): FilterAction
    {
        $body = $this->getRequestBody($request);

        return new FilterAction(
            $shop,
            $body['data']['features'],
        );
    }

    /**
     * @param array<string, mixed> $source
     */
    private function parseSource(array $source): ActionSource
    {
        if (!isset($source['url'], $source['appVersion']) || !is_string($source['url']) || !is_string($source['appVersion'])) {
            throw new MalformedWebhookBodyException();
        }

        return new ActionSource(
            $source['url'],
            $source['appVersion']
        );
    }

    /**
     * @return array
     * @throws JsonException
     */
    private function getRequestBody(RequestInterface $request): array
    {
        $body = \json_decode($request->getBody()->getContents(), true, flags: \JSON_THROW_ON_ERROR);
        $request->getBody()->rewind();

        if (!\is_array($body) || !isset($body['source']) || !\is_array($body['source'])) {
            throw new MalformedWebhookBodyException();
        }
        return $body;
    }
}
