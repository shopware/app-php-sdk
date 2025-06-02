<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\InAppPurchase;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Validator;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * @phpstan-type InAppPurchaseArray array{identifier: string, quantity: int, nextBookingDate?: string, sub: string}
 */
class InAppPurchaseProvider
{
    public function __construct(
        private readonly SBPStoreKeyFetcher $keyFetcher,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    /**
     * @param non-empty-string $encodedPurchases
     * @return Collection<InAppPurchase>
     */
    public function decodePurchases(string $encodedPurchases, ShopInterface $shop, bool $retried = false): Collection
    {
        try {
            $keys = $this->keyFetcher->getKey($retried);
            $signatureValidator = new HasValidRSAJWKSignature($keys);

            $parser = new Parser(new JoseEncoder());
            /** @var Token\Plain $token */
            $token = $parser->parse($encodedPurchases);

            $validator = new Validator();
            $validator->assert($token, $signatureValidator);

            return $this->transformClaims($token);
        } catch (\Exception $e) {
            if (!$retried) {
                return $this->decodePurchases($encodedPurchases, $shop, true);
            }

            $this->logger->error('Failed to decode in-app purchases: ' . $e->getMessage());

            return new Collection();
        }
    }

    /**
     * @return Collection<InAppPurchase>
     * @throws \Exception
     */
    private function transformClaims(Token\Plain $token): Collection
    {
        $inAppPurchases = new Collection();

        /** @var InAppPurchaseArray $inAppPurchase */
        foreach ($token->claims()->all() as $inAppPurchase) {
            if (!\array_key_exists('identifier', $inAppPurchase)) {
                continue;
            }

            $identifier = $inAppPurchase['identifier'];
            $nextBookingDate = isset($inAppPurchase['nextBookingDate']) ? new \DateTime($inAppPurchase['nextBookingDate']) : null;

            $inAppPurchaseObject = new InAppPurchase(
                $identifier,
                $inAppPurchase['quantity'],
                $nextBookingDate,
            );

            $inAppPurchases->set($identifier, $inAppPurchaseObject);
        }

        return $inAppPurchases;
    }
}
