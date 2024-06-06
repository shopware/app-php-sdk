<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\InAppPurchase;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\InAppPurchase\HasMatchingDomain;
use Shopware\App\SDK\Test\JWKSHelper;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(HasMatchingDomain::class)]
class HasMatchingDomainTest extends TestCase
{
    public function testAssert(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['sub' => 'example.com'], 'swagInAppPurchase2' => ['sub' => 'example.com']]);

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        $constraint = new HasMatchingDomain($shop);
        $constraint->assert($token);

        static::assertTrue(true);
    }

    public function testAssertWithoutSubClaim(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['foo' => 'example.com'], 'swagInAppPurchase2' => ['sub' => 'example.com']]);

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        static::expectException(ConstraintViolation::class);

        $constraint = new HasMatchingDomain($shop);
        $constraint->assert($token);
    }

    public function testAssertWithInvalidHost(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['sub' => 'wrong.com'], 'swagInAppPurchase2' => ['sub' => 'example.com']]);

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        static::expectException(ConstraintViolation::class);
        static::expectExceptionMessage('Token domain invalid: wrong.com, expected: example.com');

        $constraint = new HasMatchingDomain($shop);
        $constraint->assert($token);
    }

    public function testAssertWithWrongTokenType(): void
    {
        $token = new class () implements Token {
            public function headers(): DataSet
            {
                return new DataSet([], '');
            }

            public function isPermittedFor(string $audience): bool
            {
                return false;
            }

            public function isIdentifiedBy(string $id): bool
            {
                return false;
            }

            public function isRelatedTo(string $subject): bool
            {
                return false;
            }

            public function hasBeenIssuedBy(string ...$issuers): bool
            {
                return false;
            }

            public function hasBeenIssuedBefore(\DateTimeInterface $now): bool
            {
                return false;
            }

            public function isMinimumTimeBefore(\DateTimeInterface $now): bool
            {
                return false;
            }

            public function isExpired(\DateTimeInterface $now): bool
            {
                return false;
            }

            public function toString(): string
            {
                return 'FOO';
            }
        };

        $shop = new MockShop('shop-id', 'https://example.com', 'secret');

        static::expectException(\Exception::class);

        $constraint = new HasMatchingDomain($shop);
        $constraint->assert($token);
    }
}
