<?php

declare(strict_types=1);

namespace Shopware\App\SDK\TaxProvider;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class TaxProviderResponseBuilder
{
    /**
     * @var array<string, array<CalculatedTax>>
     */
    protected array $lineItemTaxes = [];

    /**
     * @var array<string, array<CalculatedTax>>
     */
    protected array $deliveryTaxes = [];

    /**
     * @var array<CalculatedTax>
     */
    protected array $cartPriceTaxes = [];

    public function __construct() {
    }

    public function addLineItemTax(string $uniqueIdentifier, CalculatedTax $tax): self
    {
        $this->lineItemTaxes[$uniqueIdentifier][] = $tax;
        return $this;
    }

    public function addDeliveryTax(string $uniqueIdentifier, CalculatedTax $tax): self
    {
        $this->deliveryTaxes[$uniqueIdentifier][] = $tax;
        return $this;
    }

    public function addCartTax(CalculatedTax $tax): self
    {
        $this->cartPriceTaxes[] = $tax;

        return $this;
    }

    public function buildPayload(): string
    {
        return \json_encode(get_object_vars($this), JSON_THROW_ON_ERROR);
    }

    public function build(): ResponseInterface
    {
        $psrFactory = new Psr17Factory();

        return $psrFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psrFactory->createStream($this->buildPayload()));
    }
}
