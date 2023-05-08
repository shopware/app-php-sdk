<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\Context\ActionButton\ActionButton;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Cart\CartPrice;
use Shopware\App\SDK\Context\Cart\CartTransaction;
use Shopware\App\SDK\Context\Cart\Delivery;
use Shopware\App\SDK\Context\Cart\DeliveryDate;
use Shopware\App\SDK\Context\Cart\DeliveryPosition;
use Shopware\App\SDK\Context\Cart\LineItem;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\TaxRule;
use Shopware\App\SDK\Context\ContextResolver;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Module\Module;
use Shopware\App\SDK\Context\Order\Order;
use Shopware\App\SDK\Context\Order\OrderDelivery;
use Shopware\App\SDK\Context\Order\OrderTransaction;
use Shopware\App\SDK\Context\Order\StateMachineState;
use Shopware\App\SDK\Context\Payment\PaymentCaptureAction;
use Shopware\App\SDK\Context\Payment\PaymentFinalizeAction;
use Shopware\App\SDK\Context\Payment\PaymentPayAction;
use Shopware\App\SDK\Context\Payment\PaymentValidateAction;
use Shopware\App\SDK\Context\Payment\Refund;
use Shopware\App\SDK\Context\Payment\RefundAction;
use Shopware\App\SDK\Context\Payment\RefundTransactionCapture;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Shopware\App\SDK\Context\SalesChannelContext\Country;
use Shopware\App\SDK\Context\SalesChannelContext\CountryState;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use Shopware\App\SDK\Context\SalesChannelContext\PaymentMethod;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannel;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelDomain;
use Shopware\App\SDK\Context\SalesChannelContext\Salutation;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingMethod;
use Shopware\App\SDK\Context\SalesChannelContext\TaxInfo;
use Shopware\App\SDK\Context\TaxProvider\TaxProvider;
use Shopware\App\SDK\Context\Webhook\Webhook;
use Shopware\App\SDK\Exception\MalformedWebhookBodyException;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(ContextResolver::class)]
#[CoversClass(ActionSource::class)]
#[CoversClass(Webhook::class)]
#[CoversClass(ActionButton::class)]
#[CoversClass(Module::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(MalformedWebhookBodyException::class)]
#[CoversClass(ArrayStruct::class)]
#[CoversClass(Cart::class)]
#[CoversClass(LineItem::class)]
#[CoversClass(CalculatedPrice::class)]
#[CoversClass(TaxProvider::class)]
#[CoversClass(CalculatedTax::class)]
#[CoversClass(TaxRule::class)]
#[CoversClass(CartPrice::class)]
#[CoversClass(Delivery::class)]
#[CoversClass(ShippingMethod::class)]
#[CoversClass(DeliveryDate::class)]
#[CoversClass(DeliveryPosition::class)]
#[CoversClass(Country::class)]
#[CoversClass(ShippingLocation::class)]
#[CoversClass(CartTransaction::class)]
#[CoversClass(SalesChannelContext::class)]
#[CoversClass(Currency::class)]
#[CoversClass(RoundingConfig::class)]
#[CoversClass(PaymentMethod::class)]
#[CoversClass(Customer::class)]
#[CoversClass(Salutation::class)]
#[CoversClass(Address::class)]
#[CoversClass(CountryState::class)]
#[CoversClass(TaxInfo::class)]
#[CoversClass(SalesChannel::class)]
#[CoversClass(SalesChannelDomain::class)]
#[CoversClass(PaymentPayAction::class)]
#[CoversClass(Order::class)]
#[CoversClass(OrderDelivery::class)]
#[CoversClass(OrderTransaction::class)]
#[CoversClass(StateMachineState::class)]
#[CoversClass(PaymentFinalizeAction::class)]
#[CoversClass(PaymentCaptureAction::class)]
#[CoversClass(PaymentValidateAction::class)]
#[CoversClass(RefundAction::class)]
#[CoversClass(Refund::class)]
#[CoversClass(RefundTransactionCapture::class)]
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

    public function testAssembleModuleInvalid(): void
    {
        $contextResolver = new ContextResolver();

        static::expectException(MalformedWebhookBodyException::class);
        $contextResolver->assembleModule(new Request('GET', '/'), $this->getShop());
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
