<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Test;

use Lcobucci\JWT\Signer\CannotSignPayload;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token\DataSet;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\Signature;

/**
 * Helper for testcases during which creating a valid signed JWT is required
 *
 * @codeCoverageIgnore
 * @internal
 */
final class JWKSHelper
{
    /**
     * @param array<array-key, mixed> $headers
     * @param array<array-key, mixed> $claims
     */
    public static function encodeIntoToken(array $headers, array $claims, string $signer = 'RS256'): Plain
    {
        $headers = \array_merge(['alg' => $signer, 'kid' => self::getStaticKid()], $headers);

        $b64Header = self::base64UrlEncode(\json_encode($headers, flags: \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR));
        $b64Payload = self::base64UrlEncode(\json_encode($claims, flags: \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR));

        $privateKey = InMemory::plainText(self::getPrivatePEM());

        $signature = self::getSigner($signer)->sign($b64Header . '.' . $b64Payload, $privateKey);

        if (!$signature) {
            throw new \RuntimeException('Could not sign the token');
        }

        /** @var non-empty-string $b64Signature */
        $b64Signature = self::base64UrlEncode($signature);

        return new Plain(
            new DataSet($headers, $b64Header),
            new DataSet($claims, $b64Payload),
            new Signature($signature, $b64Signature)
        );
    }

    public static function getStaticKid(): string
    {
        $jwks = self::getPublicJWKS();

        /** @var array{keys: array{0: array{kid?: non-empty-string}}} $decoded */
        $decoded = \json_decode($jwks, true, flags: \JSON_THROW_ON_ERROR);

        if (!isset($decoded['keys'][0]['kid'])) {
            throw new \RuntimeException('Could not find the kid in the JWKS');
        }

        return $decoded['keys'][0]['kid'];
    }

    /**
     * @return non-empty-string
     */
    public static function getPublicJWKS(): string
    {
        $jwks = \file_get_contents(__DIR__ . '/_fixtures/jwks.json');

        if (!$jwks) {
            throw new \RuntimeException('Could not load the JWKS');
        }

        return $jwks;
    }

    /**
     * @return non-empty-string
     */
    public static function getPrivatePEM(): string
    {
        $key = \file_get_contents(__DIR__ . '/_fixtures/jwks_private.pem');

        if (!$key) {
            throw new \RuntimeException('Could not load the private PEM');
        }

        return $key;
    }

    private static function base64UrlEncode(string $data): string
    {
        return \rtrim(\strtr(\base64_encode($data), '+/', '-_'), '=');
    }

    private static function getSigner(string $alg): Rsa
    {
        return match ($alg) {
            'RS256' => new Sha256(),
            'RS384' => new Sha384(),
            'RS512' => new Sha512(),
            default => throw new CannotSignPayload(\sprintf('Unsupported algorithm: "%s"', $alg)),
        };
    }
}
