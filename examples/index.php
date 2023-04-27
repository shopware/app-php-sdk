<?php

declare(strict_types=1);

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Registration\RandomStringShopSecretGenerator;
use Shopware\App\SDK\Registration\RegistrationService;

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

$register = new RegistrationService(
    $app,
    new FileShopRepository(),
    new RequestVerifier(),
    new ResponseSigner($app)
);

if (str_starts_with($serverRequest->getUri()->getPath(), '/register/authorize')) {
    send($register->handleShopRegistrationRequest($serverRequest, 'http://localhost:6000/register/callback'));
} elseif (str_starts_with($serverRequest->getUri()->getPath(), '/register/callback')) {
    send($register->handleConfirmation($serverRequest));
} else {
    http_response_code(404);
}
