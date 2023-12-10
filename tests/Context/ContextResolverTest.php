<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Shopware\App\SDK\Context\ContextResolver;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Payment\PaymentCaptureAction;
use Shopware\App\SDK\Context\Payment\PaymentFinalizeAction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Context\Payment\PaymentRecurringAction;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ContextResolver::class)]
class ContextResolverTest extends TestCase
{
    public function testAssembleWebhookMalformed(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleWebhook(
            $this->createApiRequest([]),
            $this->getShop()
        );
    }

    public function testAssembleWebhook(): void
    {
        $contextResolver = new ContextResolver();

        $webhook = $contextResolver->assembleWebhook(
            $this->createApiRequest([
                'source' => [
                    'url' => 'https://example.com',
                    'appVersion' => '1.0.0',
                ],
                'data' => [
                    'event' => 'order.placed',
                    'payload' => [
                        'orderId' => '123',
                    ],
                ],
                'timestamp' => 123456789,
            ]),
            $this->getShop()
        );

        static::assertSame('123', $webhook->payload['orderId']);
        static::assertSame('order.placed', $webhook->eventName);
        static::assertSame('https://example.com', $webhook->source->url);
        static::assertSame('1.0.0', $webhook->source->appVersion);
    }

    public function testAssembleActionButtonMalformed(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleActionButton(
            $this->createApiRequest([]),
            $this->getShop()
        );
    }

    public function testAssembleActionButton(): void
    {
        $contextResolver = new ContextResolver();

        $actionButton = $contextResolver->assembleActionButton(
            $this->createApiRequest([
                'source' => [
                    'url' => 'https://example.com',
                    'appVersion' => '1.0.0',
                ],
                'data' => [
                    'ids' => ['123'],
                    'entity' => 'order',
                    'action' => 'open',
                ],
            ]),
            $this->getShop()
        );

        static::assertSame(['123'], $actionButton->ids);
        static::assertSame('order', $actionButton->entity);
        static::assertSame('open', $actionButton->action);

        static::assertSame('https://example.com', $actionButton->source->url);
        static::assertSame('1.0.0', $actionButton->source->appVersion);
    }

    public function testMalformedSource(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleActionButton(
            $this->createApiRequest([
                'source' => [
                    'test' => 'https://example.com',
                ],
            ]),
            $this->getShop()
        );
    }

    public function testAssembleModule(): void
    {
        $contextResolver = new ContextResolver();

        $module = $contextResolver->assembleModule(
            new Request('GET', 'http://localhost:6001/module/test?shop-id=vvRy7Nv3Bo8mAVda&shop-url=http://localhost:8000&timestamp=1683015472&sw-version=6.5.9999999.9999999-dev&sw-context-language=2fbb5fe2e29a4d70aa5854ce7ce3e20b&sw-user-language=en-GB&shopware-shop-signature=650455d43eda4eeb4c9a12ee0eb15b46ce88776abaf9beb1ffac31be136e1d9b'),
            $this->getShop()
        );

        static::assertSame('6.5.9999999.9999999-dev', $module->shopwareVersion);
        static::assertSame('2fbb5fe2e29a4d70aa5854ce7ce3e20b', $module->contentLanguage);
        static::assertSame('en-GB', $module->userLanguage);
    }

    /**
     * @dataProvider assembleModuleInvalidRequestBodyProvider
     */
    public function testAssembleModuleInvalid(string $uri): void
    {
        $contextResolver = new ContextResolver();

        $uri = '/?' . $uri;

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleModule(new Request('GET', $uri), $this->getShop());
    }

    public function testAssembleTaxProviderInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleTaxProvider(new Request('POST', '/', [], '{}'), $this->getShop());
    }

    public function testAssembleTaxProvider(): void
    {
        $contextResolver = new ContextResolver();
        $tax = $contextResolver->assembleTaxProvider(new Request('GET', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/tax.json')), $this->getShop());

        static::assertSame('W4K2OUeCshirU015lWDfche9vymD4cUt', $tax->cart->getToken());
        static::assertNull($tax->cart->getAffiliateCode());
        static::assertNull($tax->cart->getCampaignCode());
        static::assertNull($tax->cart->getCustomerComment());

        $lineItems = $tax->cart->getLineItems();
        static::assertCount(1, $lineItems);
        static::assertArrayHasKey('0', $lineItems);
        static::assertSame('91298e263c5b4bb88c3f51c873d7e76e', $lineItems['0']->getId());
        static::assertSame('a5209fb05f4f473f9702c3868ea2deac', $lineItems['0']->getUniqueIdentifier());
        static::assertSame('product', $lineItems['0']->getType());
        static::assertIsArray($lineItems['0']->getPayload());
        static::assertSame(1, $lineItems['0']->getQuantity());
        static::assertSame('Aerodynamic Bronze Resorcerer', $lineItems['0']->getLabel());
        static::assertSame(['is-physical'], $lineItems['0']->getStates());
        static::assertSame('91298e263c5b4bb88c3f51c873d7e76e', $lineItems['0']->getReferencedId());
        static::assertSame(true, $lineItems['0']->isGood());
        static::assertSame([], $lineItems['0']->getChildren());

        $price = $lineItems['0']->getPrice();

        static::assertSame(623.53, $price->getTotalPrice());
        static::assertSame(623.53, $price->getUnitPrice());
        static::assertSame(1, $price->getQuantity());

        $calculatedTaxes = $price->getCalculatedTaxes();
        static::assertCount(1, $calculatedTaxes);
        static::assertArrayHasKey('0', $calculatedTaxes);
        static::assertSame(0.0, $calculatedTaxes['0']->getTaxRate());
        static::assertSame(0.0, $calculatedTaxes['0']->getTax());
        static::assertSame(623.53, $calculatedTaxes['0']->getPrice());

        $taxRules = $price->getTaxRules();
        static::assertCount(1, $taxRules);
        static::assertArrayHasKey('0', $taxRules);

        $taxRule = $taxRules['0'];
        static::assertSame(0.0, $taxRule->getTaxRate());
        static::assertSame(100.0, $taxRule->getPercentage());

        $price = $tax->cart->getPrice();

        static::assertSame(623.53, $price->getTotalPrice());
        static::assertSame(623.53, $price->getNetPrice());
        static::assertSame(623.53, $price->getPositionPrice());
        static::assertSame('gross', $price->getTaxStatus());
        static::assertSame(623.53, $price->getRawTotal());

        $taxRules = $price->getTaxRules();
        static::assertCount(1, $taxRules);
        static::assertArrayHasKey('0', $taxRules);

        $taxRule = $taxRules['0'];
        static::assertSame(0.0, $taxRule->getTaxRate());
        static::assertSame(100.0, $taxRule->getPercentage());

        $taxRules = $price->getCalculatedTaxes();
        static::assertCount(1, $taxRules);
        static::assertArrayHasKey('0', $taxRules);

        $taxRule = $taxRules['0'];

        static::assertSame(0.0, $taxRule->getTaxRate());
        static::assertSame(0.0, $taxRule->getTax());
        static::assertSame(623.53, $taxRule->getPrice());

        $deliveries = $tax->cart->getDeliveries();
        static::assertCount(1, $deliveries);
        static::assertArrayHasKey('0', $deliveries);

        $delivery = $deliveries['0'];

        static::assertSame('Standard', $delivery->getShippingMethod()->getName());

        $deliveryDate = $delivery->getDeliveryDate();
        static::assertSame('2023-05-03T16:00:00+00:00', $deliveryDate->getEarliest()->format(\DATE_ATOM));
        static::assertSame('2023-05-05T16:00:00+00:00', $deliveryDate->getLatest()->format(\DATE_ATOM));

        $positions = $delivery->getPositions();

        static::assertCount(1, $positions);
        static::assertArrayHasKey('0', $positions);

        $position = $positions['0'];

        static::assertSame('91298e263c5b4bb88c3f51c873d7e76e', $position->getIdentifier());
        static::assertSame(1, $position->getQuantity());
        static::assertSame(1683129600, $position->getDeliveryDate()->getEarliest()->getTimestamp());
        static::assertSame(1683302400, $position->getDeliveryDate()->getLatest()->getTimestamp());

        static::assertSame('Aerodynamic Bronze Resorcerer', $position->getLineItem()->getLabel());
        static::assertSame(1, $position->getPrice()->getQuantity());

        $location = $delivery->getLocation();

        static::assertSame('US', $location->getCountry()->getIso());

        $transactions = $tax->cart->getTransactions();

        static::assertCount(1, $transactions);
        static::assertArrayHasKey('0', $transactions);

        $transaction = $transactions['0'];

        static::assertSame(623.53, $transaction->getAmount()->getTotalPrice());
        static::assertSame('20c5b5b9ec9d4f39b36816488cd58133', $transaction->getPaymentMethodId());

        $context = $tax->context;

        static::assertSame('gross', $context->getTaxState());
        static::assertSame('W4K2OUeCshirU015lWDfche9vymD4cUt', $context->getToken());
        static::assertSame('b7d2554b0ce847cd82f3ac9bd1c0dfca', $context->getCurrencyId());

        $currency = $context->getCurrency();

        static::assertSame('b7d2554b0ce847cd82f3ac9bd1c0dfca', $currency->getId());
        static::assertSame('Euro', $currency->getName());
        static::assertSame('EUR', $currency->getIsoCode());
        static::assertSame('EUR', $currency->getShortName());
        static::assertSame('€', $currency->getSymbol());
        static::assertSame(0.0, $currency->getTaxFreeFrom());
        static::assertSame(1.0, $currency->getFactor());
        static::assertSame(true, $currency->getItemRounding()->isRoundForNet());
        static::assertSame(2, $currency->getItemRounding()->getDecimals());
        static::assertSame(0.01, $currency->getItemRounding()->getInterval());
        static::assertSame(true, $currency->getTotalRounding()->isRoundForNet());
        static::assertSame(2, $currency->getTotalRounding()->getDecimals());
        static::assertSame(0.01, $currency->getTotalRounding()->getInterval());

        $payment = $context->getPaymentMethod();
        static::assertSame('20c5b5b9ec9d4f39b36816488cd58133', $payment->getId());
        static::assertSame('Cash on delivery', $payment->getName());
        static::assertSame('Payment upon receipt of goods.', $payment->getDescription());
        static::assertNull($payment->getAvailabilityRuleId());
        static::assertTrue($payment->isActive());
        static::assertTrue($payment->isSynchronous());
        static::assertFalse($payment->isAsynchronous());
        static::assertFalse($payment->isPrepared());
        static::assertTrue($payment->isAfterOrderEnabled());
        static::assertFalse($payment->isRefundable());

        $shippingMethod = $context->getShippingMethod();
        static::assertSame('4c2016bd34a7428ba6c056a7d8721f0a', $shippingMethod->getId());
        static::assertSame('Standard', $shippingMethod->getName());
        static::assertSame('auto', $shippingMethod->getTaxType());

        $customer = $context->getCustomer();
        static::assertNotNull($customer);
        static::assertSame('8a70aff1edf94970b29b1e1a66674e58', $customer->getId());
        static::assertSame([], $customer->getCustomFields());
        static::assertSame(null, $customer->getCompany());
        static::assertSame('private', $customer->getAccountType());
        static::assertSame(false, $customer->isGuest());
        static::assertSame([], $customer->getVatIds());
        static::assertSame('::', $customer->getRemoteAddress());
        static::assertSame('Max', $customer->getFirstName());
        static::assertSame('Mustermann', $customer->getLastName());
        static::assertSame('c0928c9d2c264e3aade1fab28a9262dd', $customer->getSalutation()->getId());
        static::assertSame('mr', $customer->getSalutation()->getSalutationKey());
        static::assertSame('Mr.', $customer->getSalutation()->getDisplayName());
        static::assertSame('Dear Mr.', $customer->getSalutation()->getLetterName());
        static::assertSame(null, $customer->getTitle());
        static::assertSame('1337', $customer->getCustomerNumber());
        static::assertSame(true, $customer->isActive());
        static::assertSame('20c5b5b9ec9d4f39b36816488cd58133', $customer->getDefaultPaymentMethod()->getId());
        static::assertSame('9bb0f999bf1f4197b6b0d4da721df57b', $customer->getDefaultBillingAddress()->getId());
        static::assertSame('670254e7d23b4d79bd9829a818089e77', $customer->getDefaultShippingAddress()->getId());
        static::assertSame('9bb0f999bf1f4197b6b0d4da721df57b', $customer->getActiveBillingAddress()->getId());
        static::assertSame('670254e7d23b4d79bd9829a818089e77', $customer->getActiveShippingAddress()->getId());
        static::assertNull($customer->getActiveShippingAddress()->getAdditionalAddressLine1());
        static::assertNull($customer->getActiveShippingAddress()->getAdditionalAddressLine2());
        static::assertNull($customer->getActiveShippingAddress()->getPhoneNumber());
        static::assertNull($customer->getActiveShippingAddress()->getDepartment());
        static::assertNull($customer->getActiveShippingAddress()->getCompany());
        static::assertSame('Ebbinghoff 10', $customer->getActiveShippingAddress()->getStreet());
        static::assertSame('Schöppingen', $customer->getActiveShippingAddress()->getCity());
        static::assertSame('48624', $customer->getActiveShippingAddress()->getZipCode());
        static::assertSame('48624', $customer->getActiveShippingAddress()->getZipCode());

        $billingAddress = $customer->getActiveShippingAddress();
        static::assertSame('670254e7d23b4d79bd9829a818089e77', $billingAddress->getId());
        static::assertSame(null, $billingAddress->getTitle());
        static::assertSame('mr', $billingAddress->getSalutation()?->getSalutationKey());
        static::assertSame('Max', $billingAddress->getFirstName());
        static::assertSame('Mustermann', $billingAddress->getLastName());
        static::assertSame([], $billingAddress->getCustomFields());
        static::assertSame('United States of America', $billingAddress->getCountry()->getName());
        static::assertSame('US', $billingAddress->getCountry()->getIso());
        static::assertSame('USA', $billingAddress->getCountry()->getIso3());
        static::assertSame('b7d2554b0ce847cd82f3ac9bd1c0dfca', $billingAddress->getCountry()->getCustomerTax()->getCurrencyId());
        static::assertSame(0.0, $billingAddress->getCountry()->getCustomerTax()->getAmount());
        static::assertSame(false, $billingAddress->getCountry()->getCustomerTax()->isEnabled());
        static::assertSame(false, $billingAddress->getCountry()->getCompanyTax()->isEnabled());
        static::assertSame('040cbcada23440ebb4f6e1bebc62e421', $billingAddress->getCountryState()?->getId());
        static::assertSame('Pennsylvania', $billingAddress->getCountryState()?->getName());
        static::assertSame([], $billingAddress->getCountryState()?->getCustomFields());
        static::assertSame(1, $billingAddress->getCountryState()?->getPosition());
        static::assertSame('US-PA', $billingAddress->getCountryState()?->getShortCode());

        $salesChannel = $context->getSalesChannel();
        static::assertSame('1092ea436e764c8a97ec195fd284ad34', $salesChannel->getId());
        static::assertSame('Storefront', $salesChannel->getName());
        static::assertSame('SWSCCTJ5OUO0TKJCMVLVWFJ2OA', $salesChannel->getAccessKey());
        static::assertSame('horizontal', $salesChannel->getTaxCalculationType());
        static::assertSame('EUR', $salesChannel->getCurrency()->getShortName());

        $domains = $salesChannel->getDomains();
        static::assertCount(1, $domains);
        static::assertSame('ef8e67662fdb4b0fb9241d7bd75fe8bb', $domains[0]->getId());
        static::assertSame('http://localhost:8000', $domains[0]->getUrl());
        static::assertSame('b7d2554b0ce847cd82f3ac9bd1c0dfca', $domains[0]->getCurrencyId());
        static::assertSame([], $domains[0]->getCustomFields());
        static::assertSame('2fbb5fe2e29a4d70aa5854ce7ce3e20b', $domains[0]->getLanguageId());
        static::assertSame('684f9a80c59846228223cb76d7cb3577', $domains[0]->getSnippetSetId());

        $rounding = $context->getRounding();
        static::assertSame(2, $rounding->getDecimals());
        static::assertSame(0.01, $rounding->getInterval());
        static::assertSame(true, $rounding->isRoundForNet());

        static::assertSame('670254e7d23b4d79bd9829a818089e77', $location->getAddress()->getId());
        static::assertNull($location->getCountryState()?->getId());
    }

    public function testAssemblePay(): void
    {
        $contextResolver = new ContextResolver();

        $body = [
            'source' => [
                'url' => 'https://example.com',
                'appVersion' => 'foo',
            ],
            'order' => [
                'id' => 'foo',
            ],
            'orderTransaction' => [
                'id' => 'bar',
            ],
            'returnUrl' => 'https://example.com/return',
            'requestData' => [
                'returnId' => '123',
            ],
            'recurring' => [
                'subscriptionId' => 'baz',
                'nextSchedule' => '2023-07-18T17:00:00.000+00:00',
            ],
        ];

        $paymentPayResponse = $contextResolver->assemblePaymentPay(
            new Request('POST', '/', [], \json_encode($body, JSON_THROW_ON_ERROR)),
            $this->getShop()
        );

        static::assertInstanceOf(PaymentPayAction::class, $paymentPayResponse);
        static::assertSame('https://example.com', $paymentPayResponse->source->url);
        static::assertSame('foo', $paymentPayResponse->source->appVersion);
        static::assertSame('foo', $paymentPayResponse->order->getId());
        static::assertSame('bar', $paymentPayResponse->orderTransaction->getId());
        static::assertSame('https://example.com/return', $paymentPayResponse->returnUrl);
        static::assertSame(['returnId' => '123'], $paymentPayResponse->requestData);
        static::assertNotNull($paymentPayResponse->recurring);
        static::assertSame('baz', $paymentPayResponse->recurring->getSubscriptionId());
        static::assertEquals(new \DateTime('2023-07-18T17:00:00.000+00:00'), $paymentPayResponse->recurring->getNextSchedule());
    }

    public function testAssemblePayWithoutRecurringData(): void
    {
        $contextResolver = new ContextResolver();

        $body = [
            'source' => [
                'url' => 'https://example.com',
                'appVersion' => 'foo',
            ],
            'order' => [
                'id' => 'foo',
            ],
            'orderTransaction' => [
                'id' => 'bar',
            ],
            'returnUrl' => 'https://example.com/return',
            'requestData' => [
                'returnId' => '123',
            ],
        ];

        $paymentPayResponse = $contextResolver->assemblePaymentPay(
            new Request('POST', '/', [], \json_encode($body, JSON_THROW_ON_ERROR)),
            $this->getShop()
        );

        static::assertInstanceOf(PaymentPayAction::class, $paymentPayResponse);
        static::assertSame('https://example.com', $paymentPayResponse->source->url);
        static::assertSame('foo', $paymentPayResponse->source->appVersion);
        static::assertSame('foo', $paymentPayResponse->order->getId());
        static::assertSame('bar', $paymentPayResponse->orderTransaction->getId());
        static::assertSame('https://example.com/return', $paymentPayResponse->returnUrl);
        static::assertSame(['returnId' => '123'], $paymentPayResponse->requestData);
        static::assertNull($paymentPayResponse->recurring);
    }

    public function testAssemblePayCaptureRecurring(): void
    {
        $contextResolver = new ContextResolver();

        $body = [
            'source' => [
                'url' => 'https://example.com',
                'appVersion' => 'foo',
            ],
            'order' => [
                'id' => 'foo',
            ],
            'orderTransaction' => [
                'id' => 'bar',
            ],
            'returnUrl' => 'https://example.com/return',
            'requestData' => [
                'returnId' => '123',
            ],
        ];

        $paymentPayResponse = $contextResolver->assemblePaymentRecurringCapture(
            new Request('POST', '/', [], \json_encode($body, JSON_THROW_ON_ERROR)),
            $this->getShop()
        );

        static::assertInstanceOf(PaymentRecurringAction::class, $paymentPayResponse);
        static::assertSame('https://example.com', $paymentPayResponse->source->url);
        static::assertSame('foo', $paymentPayResponse->source->appVersion);
        static::assertSame('foo', $paymentPayResponse->order->getId());
        static::assertSame('bar', $paymentPayResponse->orderTransaction->getId());
    }

    public function testAssemblePayFinalize(): void
    {
        $contextResolver = new ContextResolver();

        $body = [
            'source' => [
                'url' => 'https://example.com',
                'appVersion' => 'foo',
            ],
            'orderTransaction' => [
                'id' => 'bar',
            ],
            'queryParameters' => [
                'returnId' => '123',
            ],
            'recurring' => [
                'subscriptionId' => 'baz',
                'nextSchedule' => '2023-07-18T17:00:00.000+00:00',
            ],
        ];

        $paymentPayResponse = $contextResolver->assemblePaymentFinalize(
            new Request('POST', '/', [], \json_encode($body, JSON_THROW_ON_ERROR)),
            $this->getShop()
        );

        static::assertInstanceOf(PaymentFinalizeAction::class, $paymentPayResponse);
        static::assertSame('https://example.com', $paymentPayResponse->source->url);
        static::assertSame('foo', $paymentPayResponse->source->appVersion);
        static::assertSame('bar', $paymentPayResponse->orderTransaction->getId());
        static::assertSame(['returnId' => '123'], $paymentPayResponse->queryParameters);
        static::assertNotNull($paymentPayResponse->recurring);
        static::assertSame('baz', $paymentPayResponse->recurring->getSubscriptionId());
        static::assertEquals(new \DateTime('2023-07-18T17:00:00.000+00:00'), $paymentPayResponse->recurring->getNextSchedule());
    }

    public function testPaymentPayCapture(): void
    {
        $contextResolver = new ContextResolver();

        $body = [
            'source' => [
                'url' => 'https://example.com',
                'appVersion' => 'foo',
            ],
            'order' => [
                'id' => 'foo',
            ],
            'orderTransaction' => [
                'id' => 'bar',
            ],
            'preOrderPayment' => [
                'returnId' => '123',
            ],
            'recurring' => [
                'subscriptionId' => 'baz',
                'nextSchedule' => '2023-07-18T17:00:00.000+00:00',
            ],
        ];

        $paymentPayResponse = $contextResolver->assemblePaymentCapture(
            new Request('POST', '/', [], \json_encode($body, JSON_THROW_ON_ERROR)),
            $this->getShop()
        );

        static::assertInstanceOf(PaymentCaptureAction::class, $paymentPayResponse);
        static::assertSame('https://example.com', $paymentPayResponse->source->url);
        static::assertSame('foo', $paymentPayResponse->source->appVersion);
        static::assertSame('bar', $paymentPayResponse->orderTransaction->getId());
        static::assertSame(['returnId' => '123'], $paymentPayResponse->requestData);
        static::assertNotNull($paymentPayResponse->recurring);
        static::assertSame('baz', $paymentPayResponse->recurring->getSubscriptionId());
        static::assertEquals(new \DateTime('2023-07-18T17:00:00.000+00:00'), $paymentPayResponse->recurring->getNextSchedule());
    }

    public function testAssemblePayInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assemblePaymentPay(
            new Request('POST', '/', [], '{}'),
            $this->getShop()
        );
    }

    public function testResolvePay(): void
    {
        $contextResolver = new ContextResolver();

        $action = $contextResolver->assemblePaymentPay(
            new Request('POST', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/payment.json')),
            $this->getShop()
        );

        static::assertSame([], $action->requestData);

        $order = $action->order;

        static::assertSame('EUR', $order->getCurrency()->getShortName());
        static::assertSame('8f69d4874c53486a95383b2126161738', $order->getId());
        static::assertSame('10077', $order->getOrderNumber());
        static::assertSame(1.0, $order->getCurrencyFactor());
        static::assertSame(1683207386, $order->getOrderDate()->getTimestamp());
        static::assertSame(395.01, $order->getPrice()->getTotalPrice());
        static::assertSame(395.01, $order->getAmountTotal());
        static::assertSame(395.01, $order->getAmountNet());
        static::assertSame(395.01, $order->getPositionPrice());
        static::assertSame('gross', $order->getTaxStatus());
        static::assertSame(0.0, $order->getShippingTotal());
        static::assertSame(0.0, $order->getShippingCosts()->getTotalPrice());
        static::assertSame('Max', $order->getOrderCustomer()->getFirstName());
        static::assertSame('Max', $order->getBillingAddress()->getFirstName());
        static::assertSame('bSMFEC_9bc6HYjzgpnXfCFP3mKAPeK7S', $order->getDeepLinkCode());
        static::assertSame(true, $order->getItemRounding()->isRoundForNet());
        static::assertSame(true, $order->getTotalRounding()->isRoundForNet());
        static::assertSame('9b2fe3ca29174971a645cafa4715c223', $order->getSalesChannelId());

        $lineItems = $order->getLineItems();

        static::assertCount(1, $lineItems);
        static::assertSame('5567f5758b414a2686afa1c6492c63a1', $lineItems[0]->getId());
        static::assertSame('Aerodynamic Bronze Slo-Cooked Prawns', $lineItems[0]->getLabel());

        $deliveries = $order->getDeliveries();

        static::assertCount(1, $deliveries);

        $delivery = $deliveries[0];
        static::assertSame([], $delivery->getTrackingCodes());
        static::assertSame(0.0, $delivery->getShippingCosts()->getTotalPrice());
        static::assertSame('Max', $delivery->getShippingOrderAddress()->getFirstName());
        static::assertSame('042855f94e95438f886e26abf714d4ac', $delivery->getStateMachineState()->getId());
        static::assertSame('open', $delivery->getStateMachineState()->getTechnicalName());
        static::assertSame(1683244800, $delivery->getShippingDateEarliest()->getTimestamp());
        static::assertSame(1683417600, $delivery->getShippingDateLatest()->getTimestamp());

        $transactions = $order->getTransactions();
        static::assertCount(1, $transactions);

        $transaction = $transactions[0];

        static::assertSame('55e858b413b54f8a97c64a040610b359', $transaction->getId());
        static::assertSame(395.01, $transaction->getAmount()->getTotalPrice());
        static::assertSame('open', $transaction->getStateMachineState()->getTechnicalName());
        static::assertSame('Payment Sync', $transaction->getPaymentMethod()->getName());
    }

    public function testAssembleFinalizeInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assemblePaymentFinalize(
            new Request('POST', '/', [], '{}'),
            $this->getShop()
        );
    }

    public function testAssembleFinalize(): void
    {
        $contextResolver = new ContextResolver();

        $action = $contextResolver->assemblePaymentFinalize(
            new Request('POST', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/payment.json')),
            $this->getShop()
        );

        static::assertSame(395.01, $action->orderTransaction->getAmount()->getTotalPrice());
    }

    public function testAssembleCaptureInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assemblePaymentCapture(
            new Request('POST', '/', [], '{}'),
            $this->getShop()
        );
    }

    public function testAssembleCapture(): void
    {
        $contextResolver = new ContextResolver();

        $action = $contextResolver->assemblePaymentCapture(
            new Request('POST', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/payment.json')),
            $this->getShop()
        );

        static::assertSame(395.01, $action->orderTransaction->getAmount()->getTotalPrice());
    }

    public function testAssembleCaptureRecurringInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assemblePaymentRecurringCapture(
            new Request('POST', '/', [], '{}'),
            $this->getShop()
        );
    }

    public function testAssembleValidationInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assemblePaymentValidate(
            new Request('POST', '/', [], '{}'),
            $this->getShop()
        );
    }

    public function testAssembleValidate(): void
    {
        $contextResolver = new ContextResolver();

        $action = $contextResolver->assemblePaymentValidate(
            new Request('POST', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/payment-validation.json')),
            $this->getShop()
        );

        static::assertSame(['tos' => 'on'], $action->requestData);
    }

    public function testAssembleRefundInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assemblePaymentRefund(
            new Request('POST', '/', [], '{}'),
            $this->getShop()
        );
    }

    public function testRefund(): void
    {
        $contextResolver = new ContextResolver();

        $action = $contextResolver->assemblePaymentRefund(
            new Request('POST', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/refund.json')),
            $this->getShop()
        );

        static::assertSame('70d9f8c7b9074445b9dd84b7b179374b', $action->refund->getId());
        static::assertSame([], $action->refund->getCustomFields());
        static::assertSame(420.69, $action->refund->getAmount()->getTotalPrice());
        static::assertNull($action->refund->getReason());
        static::assertSame('open', $action->refund->getStateMachineState()->getTechnicalName());
        static::assertNull($action->refund->getTransactionCapture()->getExternalReference());
        static::assertSame(420.69, $action->refund->getTransactionCapture()->getAmount()->getTotalPrice());
        static::assertSame('a357e3b039a046079856f6a7425ec700', $action->refund->getTransactionCapture()->getTransaction()->getId());
    }

    public function testAssembleRiskAssessment(): void
    {
        $contextResolver = new ContextResolver();

        $action = $contextResolver->assembleRiskAssessment(
            new Request('POST', '/', [], (string) file_get_contents(__DIR__ . '/_fixtures/risk-assessment.json')),
            $this->getShop()
        );

        static::assertSame([
            '018b03edb1b07149886990689ed3cc8b' => 'handler_shopware_cashpayment',
            '018b045dcdf473548d6598526c1fcfb8' => 'handler_app_swagbraintreeapp_swagbraintree_creditcard',
            '018b03edb1b37102a1a99a8064bc4aae' => 'handler_shopware_prepayment',
            '018b03edb1ac7351abcd09ed97221746' => 'handler_shopware_invoicepayment',
        ], $action->paymentMethods);

        static::assertSame([
            '018b03edb1ca7372a83e5b29b5d2674c' => 'Standard',
            '018b03edb1ca7372a83e5b29b69cd46d' => 'Express',
        ], $action->shippingMethods);
    }

    #[DataProvider('assembleStorefrontInvalidHeaders')]
    public function testStorefrontRequestMalformed(string $header): void
    {
        $contextResolver = new ContextResolver();

        $this->expectException(MalformedWebhookBodyException::class);
        $request = new Request('POST', '/', [], '{}');

        $contextResolver->assembleStorefrontRequest(
            $request->withHeader('shopware-app-token', $header),
            $this->getShop()
        );
    }

    public function testAssembleStorefrontRequest(): void
    {
        $contextResolver = new ContextResolver();

        $request = new Request('POST', '/', [], '{}');
        $request = $request->withHeader('shopware-app-token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJGcWFIV1VzQ1JOc3JaOWtRIiwiaWF0IjoxNjg5ODM3MDkyLjI3ODMyOSwibmJmIjoxNjg5ODM3MDkyLjI3ODMyOSwiZXhwIjoxNjg5ODQwNjkyLjI3ODI0Mywic2FsZXNDaGFubmVsSWQiOiIwMTg5NjQwNTU0YjU3MDBjODBjMmM0YTIwMmUyNDAxZCJ9.g8Da0bN3bkkmEdzMeXmI8wlDQEZMCDiKJvqS288B4JI');

        $action = $contextResolver->assembleStorefrontRequest(
            $request,
            $this->getShop()
        );

        static::assertSame('0189640554b5700c80c2c4a202e2401d', $action->claims->getSalesChannelId());
    }

    public function testAssembleStorefrontRequestWithEmptyTokenThrows(): void
    {
        $contextResolver = new ContextResolver();

        $request = new Request('POST', '/', [], '{}');
        $request = $request->withHeader('shopware-app-token', '');

        static::expectException(MalformedWebhookBodyException::class);

        $contextResolver->assembleStorefrontRequest(
            $request,
            $this->getShop()
        );
    }

    /**
     * @dataProvider methodsProvider
     */
    public function testBodyRewindIsCalled(string $method): void
    {
        $body = static::createMock(StreamInterface::class);
        $body
            ->expects(static::once())
            ->method('rewind');

        $body
            ->expects(static::once())
            ->method('getContents')
            ->willReturn('{}');

        $request = new Request('POST', '/', [], $body);

        static::expectException(MalformedWebhookBodyException::class);

        $contextResolver = new ContextResolver();
        $contextResolver->$method($request, $this->getShop());
    }

    /**
     * @dataProvider invalidSourceProvider
     */
    public function testParseSourceInvalid(string $source): void
    {
        $request = new Request('POST', '/', [], $source);

        $contextResolver = new ContextResolver();
        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleWebhook($request, $this->getShop());
    }

    /**
     * @return iterable<string[]>
     */
    public static function assembleStorefrontInvalidHeaders(): iterable
    {
        yield [''];
        yield ['foo'];
    }

    /**
     * @return iterable<string[]>
     */
    public static function assembleModuleInvalidRequestBodyProvider(): iterable
    {
        yield [''];
        yield ['sw-version='];
        yield ['sw-version=640'];
        yield ['sw-version=6.5.0.0&sw-context-language='];
        yield ['sw-version=6.5.0.0&sw-context-language=1'];
    }

    /**
     * @return iterable<string[]>
     */
    public static function methodsProvider(): iterable
    {
        yield ['assembleWebhook'];
        yield ['assembleActionButton'];
        yield ['assembleTaxProvider'];
        yield ['assemblePaymentPay'];
        yield ['assemblePaymentFinalize'];
        yield ['assemblePaymentCapture'];
        yield ['assemblePaymentValidate'];
        yield ['assemblePaymentRefund'];
        yield ['assemblePaymentRecurringCapture'];
        yield ['assembleRiskAssessment'];
    }

    /**
     * @return iterable<string[]>
     */
    public static function invalidSourceProvider(): iterable
    {
        yield ['{}'];
        yield ['{"source":{}}'];
        yield ['{"source":{"foo":"bar"}}'];
        yield ['{"source":{"url":1}}'];
        yield ['{"source":{"url":"https://example.com"}}'];
        yield ['{"source":{"url":"https://example.com", "foo":"bar"}}'];
        yield ['{"source":{"url":"https://example.com", "appVersion":1}}'];
    }

    private function getShop(): ShopInterface
    {
        return new MockShop('shop-id', 'shop-url', 'shop-secret');
    }

    /**
     * @param array<mixed> $json
     *
     * @throws \JsonException
     */
    private function createApiRequest(array $json): RequestInterface
    {
        return new Request('POST', 'https://example.com', [], json_encode($json, JSON_THROW_ON_ERROR));
    }
}
