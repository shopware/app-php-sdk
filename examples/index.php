<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Context\ContextResolver;
use Shopware\App\SDK\Registration\RegistrationService;
use Shopware\App\SDK\Shop\ShopResolver;

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

$serverRequest = $creator->fromGlobals();

$app = new AppConfiguration('Foo', 'test');

$fileShopRepository = new FileShopRepository();
$register = new RegistrationService(
    $app,
    $fileShopRepository,
    new RequestVerifier(),
    new ResponseSigner($app)
);
$shopResolver = new ShopResolver($fileShopRepository);
$contextResolver = new ContextResolver();

if (str_starts_with($serverRequest->getUri()->getPath(), '/register/authorize')) {
    send($register->handleShopRegistrationRequest($serverRequest, 'http://localhost:6000/register/callback'));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/register/callback')) {
    send($register->handleConfirmation($serverRequest));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/webhook/product.written')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $webhook = $contextResolver->assembleWebhook($serverRequest, $shop);
    error_log(sprintf('Got request from shop %s for event %s', $shop->getShopUrl(), $webhook->eventName));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/action/product')) {
    $shop = $shopResolver->resolveShop($serverRequest);
    $actionButton = $contextResolver->assembleActionButton($serverRequest, $shop);
    error_log(sprintf('Got request from shop %s for action %s and ids %s', $shop->getShopUrl(), $actionButton->action, implode(', ', $actionButton->ids)));
} else {
    http_response_code(404);
}
