<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Registration;

use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
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
        private readonly ShopSecretGeneratorInterface $shopSecretGeneratorInterface
    ) {
    }

    /**
     * @throws SignatureNotFoundException
     *
     * @return array{proof: string, confirmation_url: string, secret: string}
     */
    public function handleShopRegistrationRequest(RequestInterface $request, string $confirmUrl): array
    {
        $this->requestVerifier->authenticateRegistrationRequest($request, $this->appConfiguration);

        parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries['shop-id']) || !is_string($queries['shop-id']) || !isset($queries['shop-url']) || !is_string($queries['shop-url'])) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($queries['shop-id']);

        if ($shop === null) {
            $shop = $this->shopRepository->createShopFromArray(
                $queries['shop-id'],
                $queries['shop-url'],
                $this->shopSecretGeneratorInterface->generate()
            );

            $this->shopRepository->createShop($shop);
        } else {
            $this->shopRepository->updateShop($shop->withShopUrl($queries['shop-url']));
        }

        return [
            'proof' => $this->responseSigner->getRegistrationSignature($shop),
            'confirmation_url' => $confirmUrl,
            'secret' => $shop->getShopSecret(),
        ];
    }

    /**
     * @throws \JsonException
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     * @throws ShopNotFoundException
     */
    public function handleConfirmation(RequestInterface $request): void
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

        $this->shopRepository->updateShop(
            $shop->withClientKey($requestContent['apiKey'])
                ->withClientSecret($requestContent['secretKey'])
        );
    }
}
