<?php

declare(strict_types=1);

namespace Shopware\App\SDK\TaxProvider;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\Framework\Collection;

class TaxProviderResponseBuilder
{
    /**
     * @var Collection<Collection<CalculatedTax>>
     */
    protected Collection $lineItemTaxes;

    /**
     * @var Collection<Collection<CalculatedTax>>
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
        if (!$this->lineItemTaxes->has($uniqueIdentifier)) {
            $this->lineItemTaxes->set($uniqueIdentifier, new Collection());
        }

        /** @phpstan-ignore-next-line is always set at this point */
        $this->lineItemTaxes->get($uniqueIdentifier)->set((string) $tax->taxRate, $tax);
        return $this;
    }

    public function addDeliveryTax(string $uniqueIdentifier, CalculatedTax $tax): self
    {
        if (!$this->deliveryTaxes->has($uniqueIdentifier)) {
            $this->deliveryTaxes->set($uniqueIdentifier, new Collection());
        }

        /** @phpstan-ignore-next-line is always set at this point */
        $this->deliveryTaxes->get($uniqueIdentifier)->set((string) $tax->taxRate, $tax);
        return $this;
    }

    public function addCartTax(CalculatedTax $tax): self
    {
        if ($this->cartPriceTaxes->has((string) $tax->taxRate)) {
            /** @phpstan-ignore-next-line is always set at this point */
            $tax = $this->cartPriceTaxes->get((string) $tax->taxRate)->add($tax);
        }

        $this->cartPriceTaxes->set((string) $tax->taxRate, $tax);
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
