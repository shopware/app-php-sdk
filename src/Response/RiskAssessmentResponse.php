<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class RiskAssessmentResponse
{
    public const ERROR_LEVEL_NOTICE = 0;
    public const ERROR_LEVEL_WARNING = 10;
    public const ERROR_LEVEL_ERROR = 20;

    /**
     * @var array{reason: string, level: int, blockOrder: bool}[] $errors - array of errors to be added to the cart by shopware
     */
    private array $errors = [];

    /**
     * @param string[] $paymentMethods - array of payment method handlers to be blocked by shopware
     * @param string[] $shippingMethods - array of shipping method ids to be blocked by shopware
     */
    public function __construct(
        private array $paymentMethods = [],
        private array $shippingMethods = [],
    ) {
    }

    public function addBlockedPaymentMethod(string $paymentMethodHandler): void
    {
        $this->paymentMethods[] = $paymentMethodHandler;
    }

    public function addBlockedShippingMethod(string $shippingMethodId): void
    {
        $this->shippingMethods[] = $shippingMethodId;
    }

    public function addRiskAssessmentError(string $reason, int $level = self::ERROR_LEVEL_NOTICE, bool $blockOrder = false): void
    {
        $this->errors[] = ['reason' => $reason, 'level' => $level, 'blockOrder' => $blockOrder];
    }

    public static function createEmptyResponse(): ResponseInterface
    {
        return (new self())->createResponse();
    }

    public function createResponse(): ResponseInterface
    {
        $psr = new Psr17Factory();

        $data = [
            'paymentMethods' => \array_unique($this->paymentMethods),
            'shippingMethods' => \array_unique($this->shippingMethods),
            'errors' => $this->errors,
        ];

        return $psr
            ->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr->createStream(\json_encode($data, JSON_THROW_ON_ERROR)));
    }
}
