<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Registration;

use Http\Discovery\Psr17Factory;
use Nyholm\Psr7\Uri;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Authentication\DualSignatureRequestVerifier;
use Shopware\App\SDK\Authentication\RequestVerifier;
use Shopware\App\SDK\Authentication\ResponseSigner;
use Shopware\App\SDK\Event\BeforeRegistrationCompletedEvent;
use Shopware\App\SDK\Event\BeforeRegistrationStartsEvent;
use Shopware\App\SDK\Event\RegistrationCompletedEvent;
use Shopware\App\SDK\Exception\MissingShopParameterException;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Shop\ShopInterface;
use Shopware\App\SDK\Shop\ShopRepositoryInterface;

class RegistrationService
{
    /**
     * @param ShopRepositoryInterface<ShopInterface> $shopRepository
     */
    public function __construct(
        private readonly AppConfiguration $appConfiguration,
        private readonly ShopRepositoryInterface $shopRepository,
        private readonly DualSignatureRequestVerifier $dualSignatureVerifier = new DualSignatureRequestVerifier(),
        private readonly ResponseSigner $responseSigner = new ResponseSigner(),
        private readonly ShopSecretGeneratorInterface $shopSecretGeneratorInterface = new RandomStringShopSecretGenerator(),
        private readonly LoggerInterface $logger = new NullLogger(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
    }

    /**
     * @throws SignatureNotFoundException
     * @throws SignatureInvalidException
     */
    public function register(RequestInterface $request): ResponseInterface
    {
        \parse_str($request->getUri()->getQuery(), $queries);

        if (!isset($queries['shop-id'], $queries['shop-url']) || !is_string($queries['shop-id']) || !is_string($queries['shop-url']) || empty($queries['shop-id']) || empty($queries['shop-url'])) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($queries['shop-id']);

        $this->logger->info(
            'Shop registration started',
            $this->registrationLogContext($request, $queries['shop-id'], $queries['shop-url'], $shop)
        );

        try {
            $this->dualSignatureVerifier->authenticateRegistrationRequest(
                $request,
                $this->appConfiguration,
                $shop
            );
        } catch (SignatureInvalidException|SignatureNotFoundException $e) {
            $this->logger->warning(
                'Shop registration signature verification failed',
                $this->registrationLogContext($request, $queries['shop-id'], $queries['shop-url'], $shop)
                    + ['exception' => $e::class, 'verification-stage' => $e->verificationStage]
            );

            throw $e;
        }

        $secret = $this->shopSecretGeneratorInterface->generate();

        $proofParameters = [
            'shop-id' => $queries['shop-id'],
            'shop-url' => $queries['shop-url'],
        ];

        if ($shop === null) {
            $shop = $this->shopRepository
                ->createShopStruct($queries['shop-id'], $queries['shop-url'], $secret)
                ->setPendingShopSecret($secret)
                ->setPendingShopUrl($queries['shop-url']);

            $shop = $this->getSanitizedShop($shop);
            $this->eventDispatcher?->dispatch(new BeforeRegistrationStartsEvent($request, $shop));
            $this->setVerifiedWithDoubleSignature($shop, $request);
            $this->shopRepository->createShop($shop);
        } else {
            $shop->setPendingShopSecret($secret)
                ->setPendingShopUrl($this->sanitizeShopUrl($queries['shop-url'])); // don't break existing URL until confirmed.

            $shop = $this->getSanitizedShop($shop);
            $this->eventDispatcher?->dispatch(new BeforeRegistrationStartsEvent($request, $shop));

            $this->setVerifiedWithDoubleSignature($shop, $request);

            $this->shopRepository->updateShop($shop);
        }

        $this->logger->info('Shop registration request received', [
            'shop-id' => $shop->getShopId(),
            'shop-url' => $shop->getShopUrl(),
            // Raw URL as signed into the proof; differs from the sanitized shop-url when the path is normalized.
            'signed-shop-url' => $proofParameters['shop-url'],
            'shopware-version' => self::incomingShopwareVersion($request),
            'signature-payload' => implode('', [
                $proofParameters['shop-id'],
                $proofParameters['shop-url'],
                $this->appConfiguration->getAppName(),
            ]),
        ]);

        $psrFactory = new Psr17Factory();

        $data = [
            'proof' => $this->responseSigner->getRegistrationSignature($this->appConfiguration, $proofParameters),
            'confirmation_url' => $this->appConfiguration->getRegistrationConfirmUrl(),
            'secret' => $secret,
        ];

        $response = $psrFactory->createResponse(200);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psrFactory->createStream(\json_encode($data, JSON_THROW_ON_ERROR)));
    }

    /**
     * @throws \JsonException
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     * @throws ShopNotFoundException
     */
    public function registerConfirm(RequestInterface $request): ResponseInterface
    {
        /** @var array<string, mixed> $requestContent */
        $requestContent = \json_decode($request->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR);

        if (
            empty($requestContent['shopId']) ||
            empty($requestContent['apiKey']) ||
            empty($requestContent['secretKey']) ||
            !is_string($requestContent['shopId']) ||
            !is_string($requestContent['apiKey']) ||
            !is_string($requestContent['secretKey'])
        ) {
            throw new MissingShopParameterException();
        }

        $shop = $this->shopRepository->getShopFromId($requestContent['shopId']);

        if (!$shop) {
            throw new ShopNotFoundException($requestContent['shopId']);
        }

        $this->logger->info(
            'Shop registration confirmation started',
            $this->registrationLogContext($request, $requestContent['shopId'], $shop->getShopUrl(), $shop)
        );

        $request->getBody()->rewind();

        // Use dual signature verifier for registration confirmation
        try {
            $this->dualSignatureVerifier->authenticateRegistrationConfirmation($request, $shop, $this->appConfiguration);
        } catch (SignatureInvalidException|SignatureNotFoundException $e) {
            $this->logger->warning(
                'Shop registration confirmation signature verification failed',
                $this->registrationLogContext($request, $shop->getShopId(), $shop->getShopUrl(), $shop)
                    + ['exception' => $e::class, 'verification-stage' => $e->verificationStage]
            );

            throw $e;
        }

        $this->eventDispatcher?->dispatch(new BeforeRegistrationCompletedEvent($shop, $request, $requestContent));
        $pendingSecret = $shop->getPendingShopSecret();
        assert($pendingSecret !== null); // should never be null here as registration/authentication would have failed
        // this is a re-registration with secret rotation.
        if ($pendingSecret !== $shop->getShopSecret()) {
            $shop->setPreviousShopSecret($shop->getShopSecret())
                ->setShopSecret($pendingSecret)
                ->setSecretsRotatedAt(new \DateTimeImmutable());

            $this->logger->info(
                'Shop secret rotated during registration confirmation',
                $this->registrationLogContext($request, $shop->getShopId(), $shop->getShopUrl(), $shop)
            );
        }

        $pendingUrl = $shop->getPendingShopUrl();
        assert($pendingUrl !== null);

        $shop->setShopUrl($this->sanitizeShopUrl($pendingUrl))->setPendingShopUrl(null);
        $shop->setPendingShopSecret(null);
        $shop->setShopApiCredentials($requestContent['apiKey'], $requestContent['secretKey']);
        $shop->setRegistrationConfirmed();

        $this->shopRepository->updateShop($shop);

        $this->logger->info('Shop registration confirmed', [
            'shop-id' => $shop->getShopId(),
            'shop-url' => $shop->getShopUrl(),
            'shopware-version' => self::incomingShopwareVersion($request),
        ]);

        $this->eventDispatcher?->dispatch(new RegistrationCompletedEvent($request, $shop));

        return (new Psr17Factory())->createResponse(204);
    }

    private function sanitizeShopUrl(string $shopUrl): string
    {
        $uri = new Uri($shopUrl);
        $path = preg_replace('#/{2,}#', '/', $uri->getPath()) ?? '';
        $uri = $uri->withPath($path);

        return (string)$uri;
    }

    private function getSanitizedShop(ShopInterface $shop): ShopInterface
    {
        return $shop->setShopUrl($this->sanitizeShopUrl($shop->getShopUrl()));
    }

    /**
     * @return array<string, bool|string|null>
     */
    private function registrationLogContext(RequestInterface $request, string $shopId, string $shopUrl, ?ShopInterface $shop = null): array
    {
        return [
            'shop-id' => $shopId,
            'shop-url' => $shopUrl,
            'shop-exists' => $shop !== null,
            'registration-confirmed' => $shop?->isRegistrationConfirmed(),
            'has-pending-secret' => $shop !== null ? $shop->getPendingShopSecret() !== null : null,
            'has-previous-secret' => $shop !== null ? $shop->getPreviousShopSecret() !== null : null,
            'enforce-double-signature' => $this->appConfiguration->enforceDoubleSignature(),
            'has-verified-with-double-signature' => $shop?->hasVerifiedWithDoubleSignature(),
            'shopware-version' => self::incomingShopwareVersion($request),
        ];
    }

    /**
     * The Shopware version that sent the registration request, read from the `sw-version` header
     * (Shopware sends it as a header on the register and confirm calls). Null when absent.
     */
    private static function incomingShopwareVersion(RequestInterface $request): ?string
    {
        $version = $request->getHeaderLine('sw-version');

        return $version !== '' ? $version : null;
    }

    /**
     * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
     */
    private function setVerifiedWithDoubleSignature(ShopInterface $shop, RequestInterface $request): void
    {
        if ($this->appConfiguration->enforceDoubleSignature() || $request->hasHeader(RequestVerifier::SHOPWARE_SHOP_SIGNATURE_HEADER)) {
            $shop->setVerifiedWithDoubleSignature();
        }
    }
}
