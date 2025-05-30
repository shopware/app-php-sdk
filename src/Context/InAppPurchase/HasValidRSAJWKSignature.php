<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Context\InAppPurchase;

use Lcobucci\JWT\Signer\InvalidKeyProvided;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Strobotti\JWK\Key\KeyInterface;
use Strobotti\JWK\Key\Rsa as RsaKey;
use Strobotti\JWK\KeyConverter;
use Strobotti\JWK\KeySet;

class HasValidRSAJWKSignature implements Constraint
{
    private const ALGORITHMS = ['RS256', 'RS384', 'RS512'];

    public function __construct(private readonly KeySet $keys)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function assert(Token $token): void
    {
        $this->validateAlgorithm($token);

        $key = $this->getValidKey($token);

        /** @var non-empty-string $pem */
        $pem = $this->convertToPem($key);

        $signer = $this->getSigner($token->headers()->get('alg'));

        (new SignedWith($signer, InMemory::plainText($pem)))->assert($token);
    }

    private function validateAlgorithm(Token $token): void
    {
        /** @var string $alg */
        $alg = $token->headers()->get('alg');

        if (!\in_array($alg, self::ALGORITHMS, true)) {
            throw new InvalidKeyProvided(\sprintf('Invalid algorithm (alg) in JWT header: "%s"', $alg));
        }
    }

    private function getValidKey(Token $token): RsaKey
    {
        /** @var string $kid */
        $kid = $token->headers()->get('kid');

        if (!$kid) {
            throw new InvalidKeyProvided('Key ID (kid) missing from JWT header');
        }

        /** @var RsaKey|null $key */
        $key = $this->keys->getKeyById($kid);

        return $key ?? throw new InvalidKeyProvided(\sprintf('Key with ID (kid) "%s" not found in JWKS', $kid));
    }

    private function convertToPem(KeyInterface $key): string
    {
        return (new KeyConverter())->keyToPem($key);
    }

    private function getSigner(string $alg): Rsa
    {
        return match ($alg) {
            default => new Sha256(),
            'RS384' => new Sha384(),
            'RS512' => new Sha512(),
        };
    }
}
