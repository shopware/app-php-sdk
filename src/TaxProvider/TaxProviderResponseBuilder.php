<?php

declare(strict_types=1);

namespace Shopware\App\SDK\TaxProvider;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Framework\Collection;

class TaxProviderResponseBuilder
{
    /**
     * @var Collection<CalculatedTax>
     */
    protected Collection $lineItemTaxes;

    /**
     * @var Collection<CalculatedTax>
     */
    protected Collection $deliveryTaxes;

    /**
     * @var Collection<CalculatedTax>
     */
    protected Collection $cartPriceTaxes;

    public function __construct()
    {
        $this->cartPriceTaxes = new Collection();
        $this->deliveryTaxes = new Collection();
        $this->lineItemTaxes = new Collection();
    }

    public function addLineItemTax(string $uniqueIdentifier, CalculatedTax $tax): self
    {
        $this->lineItemTaxes->set($uniqueIdentifier, $tax);
        return $this;
    }

    public function addDeliveryTax(string $uniqueIdentifier, CalculatedTax $tax): self
    {
        $this->deliveryTaxes->set($uniqueIdentifier, $tax);
        return $this;
    }

    public function addCartTax(CalculatedTax $tax): self
    {
        $this->cartPriceTaxes->add($tax);
        return $this;
    }

    public function buildPayload(): string
    {
        return \json_encode(\get_object_vars($this), \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \JsonException
     */
    public function build(): ResponseInterface
    {
        $psrFactory = new Psr17Factory();

        return $psrFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psrFactory->createStream($this->buildPayload()));
    }
}
