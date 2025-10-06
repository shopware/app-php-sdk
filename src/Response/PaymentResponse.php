<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Response;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class PaymentResponse
{
    public const ACTION_CANCEL = 'cancel';
    public const ACTION_FAIL = 'fail';
    public const ACTION_PAID = 'paid';
    public const ACTION_PAID_PARTIALLY = 'paid_partially';
    public const ACTION_PROCESS = 'process';
    public const ACTION_PROCESS_UNCONFIRMED = 'process_unconfirmed';
    public const ACTION_REFUND = 'refund';
    public const ACTION_REMIND = 'remind';
    public const ACTION_REOPEN = 'reopen';
    public const ACTION_AUTHORIZE = 'authorize';
    public const ACTION_CHARGEBACK = 'chargeback';

    /**
     * @param array<mixed> $data - Data that will be saved on the order to identify the payment
     */
    public static function validateSuccess(array $data): ResponseInterface
    {
        return self::createResponse(['preOrderPayment' => $data]);
    }

    public static function validationError(string $message): ResponseInterface
    {
        return self::createResponse(['message' => $message]);
    }

    public static function paid(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_PAID);
    }

    public static function paidPartially(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_PAID_PARTIALLY);
    }

    public static function cancelled(string $message = ''): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_CANCEL, $message);
    }

    public static function failed(string $message = ''): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_FAIL, $message);
    }

    public static function authorize(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_AUTHORIZE);
    }

    public static function unconfirmed(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_PROCESS_UNCONFIRMED);
    }

    public static function inProgress(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_PROCESS);
    }

    public static function refunded(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_REFUND);
    }

    public static function reminded(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_REMIND);
    }

    public static function chargeback(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_CHARGEBACK);
    }

    public static function reopen(): ResponseInterface
    {
        return self::createStatusResponse(self::ACTION_REOPEN);
    }

    /**
     * @param self::ACTION_* $status
     */
    public static function createStatusResponse(string $status, string $message = ''): ResponseInterface
    {
        return self::createResponse(array_filter(['status' => $status, 'message' => $message]));
    }

    /**
     * @param self::ACTION_*|'' $status
     */
    public static function redirect(string $url, string $status = ''): ResponseInterface
    {
        return self::createResponse(array_filter(['redirectUrl' => $url, 'status' => $status]));
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
