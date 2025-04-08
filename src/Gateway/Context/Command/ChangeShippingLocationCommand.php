<?php declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Changes the shipping location of the context (even when no shipping address is given yet).
 * Especially useful for rule system shipping costs calculations for non logged in customers.
 * Beware that the shipping location is overridden by the customers shipping address during login or checkout.
 */
class ChangeShippingLocationCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_change-shipping-location';

    /**
     * @param string|null $countryIso - The ISO 3166-1 alpha-3 or alpha-2 country code (3-letter or 2-letter codes), e.g. DE or DEU, US or USA, GB or GBR
     * @param string|null $countryStateIso - The ISO 3166-2 state code (2-letter code), e.g. DE-BW, US-CA, GB-ENG
     */
    public function __construct(
        public ?string $countryIso = null,
        public ?string $countryStateIso = null,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('countryIso', $countryIso);
        $this->setPayloadValue('countryStateIso', $countryStateIso);
    }
}