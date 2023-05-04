<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\AppLifecycle;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Context\ContextResolver;
use Shopware\App\SDK\Payment\PaymentResponse;
use Shopware\App\SDK\Registration\RegistrationService;
use Shopware\App\SDK\Shop\ShopResolver;
use Shopware\App\SDK\TaxProvider\CalculatedTax;
use Shopware\App\SDK\TaxProvider\TaxProviderResponseBuilder;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helper.php';
require __DIR__ . '/FileRepository.php';

$psr17Factory = new Psr17Factory();

$creator = new ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory
);

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    error_log($errstr);
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
});

$serverRequest = $creator->fromGlobals();

$app = new AppConfiguration('Foo', 'test', 'http://localhost:6001/register/callback');

$fileShopRepository = new FileShopRepository();
$registrationService = new RegistrationService($app, $fileShopRepository);
$shopResolver = new ShopResolver($fileShopRepository);
$appLifecycle = new AppLifecycle($registrationService, $shopResolver, $fileShopRepository);
$contextResolver = new ContextResolver();

if (str_starts_with($serverRequest->getUri()->getPath(), '/register/authorize')) {
    send($appLifecycle->register($serverRequest));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/register/callback')) {
    send($appLifecycle->registerConfirm($serverRequest));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/webhook/app.deleted')) {
    send($appLifecycle->uninstall($serverRequest));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/webhook/app.activated')) {
    send($appLifecycle->activate($serverRequest));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/webhook/app.deactivated')) {
    send($appLifecycle->deactivate($serverRequest));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/webhook/product.written')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $webhook = $contextResolver->assembleWebhook($serverRequest, $shop);

    error_log(sprintf('Got request from shop %s for event %s', $shop->getShopUrl(), $webhook->eventName));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/tax/process')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $taxProviderContext = $contextResolver->assembleTaxProvider($serverRequest, $shop);

    $builder = new TaxProviderResponseBuilder();
    $signer = new ResponseSigner();

    // Add tax for each line item
    foreach ($taxProviderContext->cart->getLineItems() as $item) {
        $taxRate = 50;

        $taxProviderContext = $item->getPrice()->getTotalPrice() * $taxRate / 100;

        $builder->addLineItemTax($item->getUniqueIdentifier(), new CalculatedTax(
            $taxProviderContext,
            $taxRate,
            $item->getPrice()->getTotalPrice()
        ));
    }

    send($signer->signResponse($builder->build(), $shop));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/payment/pay')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $payment = $contextResolver->assemblePaymentPay($serverRequest, $shop);

    // do payment stuff
    error_log($payment->order->getOrderNumber());

    $signer = new ResponseSigner();

    send($signer->signResponse(PaymentResponse::paid(), $shop));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/payment/async-pay')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $payment = $contextResolver->assemblePaymentPay($serverRequest, $shop);

    // do payment stuff
    error_log($payment->order->getOrderNumber());

    $signer = new ResponseSigner();

    send($signer->signResponse(PaymentResponse::redirect($payment->returnUrl), $shop));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/payment/finalize')) {
    $shop = $shopResolver->resolveShop($serverRequest);

    $payment = $contextResolver->assemblePaymentFinalize($serverRequest, $shop);

    $signer = new ResponseSigner();

    send($signer->signResponse(PaymentResponse::paid(), $shop));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/payment/validate')) {
    $shop = $shopResolver->resolveShop($serverRequest);

    $payment = $contextResolver->assemblePaymentValidate($serverRequest, $shop);

    $signer = new ResponseSigner();

    send($signer->signResponse(PaymentResponse::validateSuccessResponse(['myValue' => 1]), $shop));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/payment/capture')) {
    $shop = $shopResolver->resolveShop($serverRequest);

    $capture = $contextResolver->assemblePaymentCapture($serverRequest, $shop);

    error_log((string)$capture->requestData['myValue']);

    $signer = new ResponseSigner();

    send($signer->signResponse(PaymentResponse::paid(), $shop));
} elseif(str_starts_with($serverRequest->getUri()->getPath(), '/module/test')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $module = $contextResolver->assembleModule($serverRequest, $shop);

    error_log(sprintf('Got module request language: %s, user language: %s', $module->contentLanguage, $module->userLanguage));

    header('Content-Type: text/html');
    echo '<h1>Hello World</h1>';
    echo "<script>
    window.parent.postMessage('sw-app-loaded', '*');
    </script>";
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/action/product')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $actionButton = $contextResolver->assembleActionButton($serverRequest, $shop);
    error_log(sprintf('Got request from shop %s for action %s and ids %s', $shop->getShopUrl(), $actionButton->action, implode(', ', $actionButton->ids)));
} else {
    http_response_code(404);
}
