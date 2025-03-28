<?php declare(strict_types=1);

namespace Shopware\App\SDK\Gateway\Context\Command;

use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

/**
 * Changes the active language for logged in or newly registered customers
 * Be aware that the language must be available in the sales channel
 */
class ChangeLanguageCommand extends ContextGatewayCommand
{
    final public const KEY = 'context_change-language';

    /**
     * @param string $iso - The BCP 47 language tag, e.g. en-US, de-DE, en-GB
     */
    public function __construct(
        public string $iso,
    ) {
        $this->keyName = self::KEY;
        $this->setPayloadValue('iso', $iso);
    }
}