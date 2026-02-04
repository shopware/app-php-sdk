<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Authentication;

use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\AppConfiguration;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use Shopware\App\SDK\Shop\ShopInterface;

class DualSignatureRequestVerifier
{
    private const INFLIGHT_ALLOWANCE = 60; // 1 minute

    private const SHOPWARE_SHOP_SIGNATURE_PREVIOUS_HEADER = 'shopware-shop-signature-previous';

    public function __construct(private readonly RequestVerifier $primaryVerifier = new RequestVerifier())
    {
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticatePostRequest(RequestInterface $request, ShopInterface $shop): void
    {
        try {
            $this->primaryVerifier->authenticatePostRequest($request, $shop->getShopSecret());
        } catch (SignatureInvalidException $exception) {
            $this->authenticateWithPreviousSecret($request, $shop, $exception, function (RequestInterface $request, string $secret) {
                $this->primaryVerifier->authenticatePostRequest($request, $secret);
            });
        }
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateGetRequest(RequestInterface $request, ShopInterface $shop): void
    {
        try {
            $this->primaryVerifier->authenticateGetRequest($request, $shop->getShopSecret());
        } catch (SignatureInvalidException $exception) {
            $this->authenticateWithPreviousSecret($request, $shop, $exception, function (RequestInterface $request, string $secret) {
                $this->primaryVerifier->authenticateGetRequest($request, $secret);
            });
        }
    }

    /**
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateStorefrontRequest(RequestInterface $request, string $shopId, ShopInterface $shop): void
    {
        try {
            $this->primaryVerifier->authenticateStorefrontRequest($request, $shopId, $shop->getShopSecret());
        } catch (RequiredConstraintsViolated $exception) {
            $this->authenticateWithPreviousSecret($request, $shop, $exception, function (RequestInterface $request, string $secret) use ($shopId) {
                $this->primaryVerifier->authenticateStorefrontRequest($request, $shopId, $secret);
            });
        }
    }

    /**
     * Helper method to authenticate with the previous secret during rotation window
     *
     * @param callable(RequestInterface, string): void $authenticator
     * @throws SignatureInvalidException
     */
    private function authenticateWithPreviousSecret(
        RequestInterface $request,
        ShopInterface $shop,
        SignatureInvalidException|RequiredConstraintsViolated $exception,
        callable $authenticator
    ): void {
        $rotatedAt = $shop->getSecretsRotatedAt();
        $previousSecret = $shop->getPreviousShopSecret();

        // No previous secret or rotation timestamp available
        if ($previousSecret === null || $rotatedAt === null) {
            throw $exception;
        }

        // Check if we're still within the inflight allowance window
        $allowanceEnd = $rotatedAt->modify(sprintf("+%d seconds", self::INFLIGHT_ALLOWANCE));

        if ((new \DateTimeImmutable()) >= $allowanceEnd) {
            throw $exception;
        }

        // Try authenticating with the previous secret
        $authenticator($request, $previousSecret);
    }

    /**
     * Authenticate registration confirmation request
     *
     * For NEW shops: verifies only with the current secret (standard header)
     * For OLD shops (re-registration): verifies with pending secret (standard header) AND optionally current secret (previous header) if double signature is enforced
     *
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateRegistrationConfirmation(RequestInterface $request, ShopInterface $shop, AppConfiguration $appConfiguration): void
    {
        $pendingSecret = $shop->getPendingShopSecret();
        // Missing registration step, during registration confirmation the pending secret must be set.
        if ($pendingSecret === null) {
            throw new SignatureInvalidException($request);
        }

        // New registration: that is not yet confirmed from shop, verify with secret shared during registration handshake is sufficient.
        $this->primaryVerifier->authenticatePostRequest($request, $pendingSecret);
        if (! $shop->isRegistrationConfirmed()) {
            return;
        }

        // OLD SHOP RE-REGISTRATION: If double signature is enforced, also verify with OLD current secret (the secret that the shop is actively using).
        if ($this->shouldEnforceDoubleSignatureForRegisterConfirm($appConfiguration, $shop, $request)) {
            $this->primaryVerifier->authenticatePostRequest($request, $shop->getShopSecret(), self::SHOPWARE_SHOP_SIGNATURE_PREVIOUS_HEADER);
        }
    }

    /**
     * Authenticate registration request
     *
     * For NEW shops: verifies only with the app secret
     * For OLD shops (re-registration): verifies with app secret AND current shop secret
     *
     * @throws SignatureInvalidException
     * @throws SignatureNotFoundException
     */
    public function authenticateRegistrationRequest(
        RequestInterface $request,
        AppConfiguration $appConfiguration,
        ?ShopInterface $shop = null
    ): void {
        // Always verify app signature first
        $this->primaryVerifier->authenticateRegistrationRequest($request, $appConfiguration->getAppSecret());

        // If there's a confirmed registration and double signature is enforced, also verify with shop's current secret
        if ($shop?->isRegistrationConfirmed() === true && $this->shouldEnforceDoubleSignatureForRegister($appConfiguration, $shop, $request)) {
            $this->primaryVerifier->authenticateRegistrationRequestWithShopSignature($request, $shop->getShopSecret());
        }
    }

    /**
     * @deprecated tag:v6.0.0 - Will be removed. Double signature verification will always be enforced.
     *
     * 1. If the shop has previously verified with a double signature, it must always do so
     * 2. If the shop is sending a double signature, then we should also verify it
     * 3. If the app is configured to enforce double signature, then we should force it
     */
    private function shouldEnforceDoubleSignature(
        AppConfiguration $config,
        ShopInterface $shop,
        RequestInterface $request,
        string $header
    ): bool {
        return $shop->hasVerifiedWithDoubleSignature()
            || $request->hasHeader($header)
            || $config->enforceDoubleSignature();
    }

    private function shouldEnforceDoubleSignatureForRegister(AppConfiguration $config, ShopInterface $shop, RequestInterface $request): bool
    {
        return $this->shouldEnforceDoubleSignature($config, $shop, $request, RequestVerifier::SHOPWARE_SHOP_SIGNATURE_HEADER);
    }

    private function shouldEnforceDoubleSignatureForRegisterConfirm(AppConfiguration $config, ShopInterface $shop, RequestInterface $request): bool
    {
        return $this->shouldEnforceDoubleSignature($config, $shop, $request, self::SHOPWARE_SHOP_SIGNATURE_PREVIOUS_HEADER);
    }

}
