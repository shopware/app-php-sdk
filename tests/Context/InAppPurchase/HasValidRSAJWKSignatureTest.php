<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\InAppPurchase;

use Lcobucci\JWT\Signer\InvalidKeyProvided;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Validation\ConstraintViolation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\InAppPurchase\HasValidRSAJWKSignature;
use Shopware\App\SDK\Test\JWKSHelper;
use Strobotti\JWK\Key\Rsa;
use Strobotti\JWK\KeySet;
use Strobotti\JWK\KeySetFactory;

#[CoversClass(HasValidRSAJWKSignature::class)]
class HasValidRSAJWKSignatureTest extends TestCase
{
    private KeySet $jwks;

    public function setUp(): void
    {
        $this->jwks = (new KeySetFactory())->createFromJSON(JWKSHelper::getPublicJWKS());
    }

    public function testAssert(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['sub' => 'example.com']]);

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);

        static::assertTrue(true);
    }

    public function testAssertWithDisallowedAlgorithm(): void
    {
        $token = JWKSHelper::encodeIntoToken(['alg' => 'HS256'], ['swagInAppPurchase' => ['sub' => 'example.com']]);

        static::expectException(InvalidKeyProvided::class);

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);
    }

    public function testAssertWithUpdatedJWKS(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['sub' => 'example.com']]);

        $key = new Rsa();
        $key->setKeyId(JWKSHelper::getStaticKid());
        $key->setExponent('AQAB');
        $key->setModulus('8z9oOF1FBpO5UudDLV8TgAwrq6abARYdEOSonBSb52H7S0Bn9aTBy4hk5h0Yn2Z2JvWVkQ9nj2CTXnawZYiygNpLoIXoOr24zUUMsZj2EERKOa8PqiPRue4FWXsDmgc0Htjj7UlE2qKRAFhxMyDkeOIICnJvi71Vpqp_eaUAvsp7KUCW-XEyZUUOP6dg84iTvM31l0KLmXAEpEYk3TWjX0Whs92H3jH3_ZMLdthURKef0MFUvGnr9Bg7cdvgGSsNVekpnb3k5XO2AiXTltA2HJVMmUh0b_usW7SSeDHdS61eDcPMG9H68UjqTaVtpYd1-TogZU6Fl8wRqpvohduaMw');
        $key->setPublicKeyUse('sig');

        $jwks = new KeySet();
        $jwks->addKey($key);

        static::expectException(ConstraintViolation::class);

        $constraint = new HasValidRSAJWKSignature($jwks);
        $constraint->assert($token);
    }

    public function testAssertWithRS384(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['sub' => 'example.com']], 'RS384');

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);

        static::assertTrue(true);
    }

    public function testAssertWithRS512(): void
    {
        $token = JWKSHelper::encodeIntoToken([], ['swagInAppPurchase' => ['sub' => 'example.com']], 'RS512');

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);

        static::assertTrue(true);
    }

    public function testAssertWithoutKid(): void
    {
        $token = JWKSHelper::encodeIntoToken(['kid' => ''], ['swagInAppPurchase' => ['sub' => 'example.com']]);

        static::expectException(InvalidKeyProvided::class);
        static::expectExceptionMessage('Key ID (kid) missing from JWT header');

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);
    }

    public function testAssertWithWrongKid(): void
    {
        $token = JWKSHelper::encodeIntoToken(['kid' => 'FOO'], ['swagInAppPurchase' => ['sub' => 'example.com']]);

        static::expectException(InvalidKeyProvided::class);
        static::expectExceptionMessage('Key with ID (kid) "FOO" not found in JWKS');

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);
    }

    public function testAssertWithWrongTokenType(): void
    {
        $token = new class () implements Token {
            public function headers(): DataSet
            {
                return new DataSet([
                    'alg' => 'RS256',
                    'kid' => JWKSHelper::getStaticKid(),
                ], '');
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

        static::expectException(\Exception::class);
        static::expectExceptionMessage('You should pass a plain token');

        $constraint = new HasValidRSAJWKSignature($this->jwks);
        $constraint->assert($token);
    }
}
