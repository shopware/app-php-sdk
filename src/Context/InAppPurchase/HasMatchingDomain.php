<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\InAppPurchase;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Shopware\App\SDK\Shop\ShopInterface;

/**
 * @phpstan-import-type InAppPurchaseArray from InAppPurchaseProvider
 */
class HasMatchingDomain implements Constraint
{
    public function __construct(private readonly ShopInterface $shop)
    {
    }

    public function assert(Token $token): void
    {
        if (!$token instanceof UnencryptedToken) {
            throw new \Exception('Incorrect token type');
        }

        /** @var InAppPurchaseArray $inAppPurchase */
        foreach ($token->claims()->all() as $inAppPurchase) {
            if (!\array_key_exists('sub', $inAppPurchase)) {
                throw ConstraintViolation::error('Missing sub claim', $this);
            }

            $host = \parse_url($this->shop->getShopUrl(), \PHP_URL_HOST);

            if ($inAppPurchase['sub'] !== $host) {
                throw ConstraintViolation::error('Token domain invalid: ' . $inAppPurchase['sub'] . ', expected: ' . $host, $this);
            }
        }
    }
}
