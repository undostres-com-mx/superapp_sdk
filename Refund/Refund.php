<?php

namespace UDT\Refund;

use Exception;
use UDT\Utils\DataUtils;

class Refund
{
    /**
     * COMMUNICATE WITH SERVER TO REQUEST REFUND AN ORDER AND VALIDATES DATA
     *
     * @return object
     * @throws Exception
     */
    public static function request($urlRefund, $appKey, $appToken, $paymentId, $transactionId, $value)
    {
        if ($paymentId === null || $transactionId === null || $value === null) throw new Exception("Payload not set.", 500);
        $urlRefund = str_replace("{paymentId}", $paymentId, $urlRefund);
        $json = [
            "paymentId" => $paymentId,
            "transactionId" => $transactionId,
            "value" => DataUtils::formatMoney($value),
            "requestId" => $paymentId . date("YmdHisu"),
        ];
        $response = DataUtils::request($urlRefund, $appKey, $appToken, $json);
        DataUtils::validateData($response, "SuperappRefundPaymentResponse.json");
        return $response;
    }
}
