<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Payment;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class PaymentResponse
{
    /**
     * @param array<mixed> $data - Data that will be saved on the order to identify the payment
     */
    public static function validateSuccessResponse(array $data): ResponseInterface
    {
        return self::createResponse(['preOrderPayment' => $data]);
    }

    public static function validationError(string $message): ResponseInterface
    {
        return self::createResponse(['message' => $message]);
    }

    public static function paid(): ResponseInterface
    {
        return self::createStatusResponse('paid');
    }

    public static function paidPartially(): ResponseInterface
    {
        return self::createStatusResponse('paid_partially');
    }

    public static function cancelled(string $message = ''): ResponseInterface
    {
        return self::createStatusResponse('cancelled', $message);
    }

    public static function failed(string $message = ''): ResponseInterface
    {
        return self::createStatusResponse('failed', $message);
    }

    public static function authorized(): ResponseInterface
    {
        return self::createStatusResponse('authorized');
    }

    public static function unconfirmed(): ResponseInterface
    {
        return self::createStatusResponse('unconfirmed');
    }

    public static function inProgress(): ResponseInterface
    {
        return self::createStatusResponse('in_progress');
    }

    public static function refunded(): ResponseInterface
    {
        return self::createStatusResponse('refunded');
    }

    public static function reminded(): ResponseInterface
    {
        return self::createStatusResponse('reminded');
    }

    public static function chargeback(): ResponseInterface
    {
        return self::createStatusResponse('chargeback');
    }

    private static function createStatusResponse(string $status, string $message = ''): ResponseInterface
    {
        return self::createResponse(array_filter(['status' => $status, 'message' => $message]));
    }

    public static function redirect(string $url): ResponseInterface
    {
        return self::createResponse(['redirectUrl' => $url]);
    }

    /**
     * @param array<mixed> $data
     */
    private static function createResponse(array $data): ResponseInterface
    {
        $psr = new Psr17Factory();

        return $psr->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($psr->createStream(json_encode($data, JSON_THROW_ON_ERROR)));
    }
}
