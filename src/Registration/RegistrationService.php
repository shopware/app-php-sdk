<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Registration;

use Http\Discovery\Psr17Factory;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Event\RegistrationBeforeCompletedEvent;
use Shopware\App\SDK\Event\RegistrationCompletedEvent;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;

class RegistrationService
{
    public function __construct(
        private readonly AppConfiguration $appConfiguration,
        private readonly ShopRepositoryInterface $shopRepository,
        private readonly RequestVerifier $requestVerifier,
        private readonly ResponseSigner $responseSigner,
        private readonly ShopSecretGeneratorInterface $shopSecretGeneratorInterface,
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
    }

    /**
     * @throws SignatureNotFoundException
     * @throws SignatureInvalidException
     */
    public function handleShopRegistrationRequest(RequestInterface $request, string $confirmUrl): ResponseInterface
    {
        $this->requestVerifier->authenticateRegistrationRequest($request, $this->appConfiguration);

        parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries['shop-id']) || !is_string($queries['shop-id']) || !isset($queries['shop-url']) || !is_string($queries['shop-url'])) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($queries['shop-id']);

        if ($shop === null) {
            $shop = $this->shopRepository->createShopStruct(
                $queries['shop-id'],
                $queries['shop-url'],
                $this->shopSecretGeneratorInterface->generate()
            );

            $this->shopRepository->createShop($shop);
        } else {
            $this->shopRepository->updateShop($shop->withShopUrl($queries['shop-url']));
        }

        $this->logger->info('Shop registration request received', [
            'shop-id' => $shop->getShopId(),
            'shop-url' => $shop->getShopUrl(),
        ]);


        $psrFactory = new Psr17Factory();

        $data = [
            'proof' => $this->responseSigner->getRegistrationSignature($shop),
            'confirmation_url' => $confirmUrl,
            'secret' => $shop->getShopSecret(),
        ];

        $response = $psrFactory->createResponse(200);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psrFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
    }

    /**
     * @throws \JsonException
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     * @throws ShopNotFoundException
     */
    public function handleConfirmation(RequestInterface $request): ResponseInterface
    {
        /** @var array<string, mixed> $requestContent */
        $requestContent = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (!isset($requestContent['shopId']) || !is_string($requestContent['shopId']) || !isset($requestContent['apiKey']) || !is_string($requestContent['apiKey']) || !isset($requestContent['secretKey']) || !is_string($requestContent['secretKey'])) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($requestContent['shopId']);

        if (!$shop) {
            throw new ShopNotFoundException();
        }

        $request->getBody()->rewind();

        $this->requestVerifier->authenticatePostRequest($request, $shop);

        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(new RegistrationBeforeCompletedEvent($shop, $request, $requestContent));
        }

        $this->shopRepository->updateShop(
            $shop->withShopApiCredentials($requestContent['apiKey'], $requestContent['secretKey'])
        );

        $this->logger->info('Shop registration confirmed', [
            'shop-id' => $shop->getShopId(),
            'shop-url' => $shop->getShopUrl(),
        ]);

        if ($this->eventDispatcher !== null) {
            $this->eventDispatcher->dispatch(new RegistrationCompletedEvent($shop, $request));
        }

        $psrFactory = new Psr17Factory();

        return $psrFactory->createResponse(204);
    }
}
