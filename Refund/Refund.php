<?php

namespace UDT\Refund;

use Exception;
use UDT\Utils\DataUtils;

class Refund
{
    /**
     * Communicate with server to request a new payment.
     *
     * @return object
     * @throws Exception if the payment is unable to request.
     */
    public static function request($urlRefund, $appKey, $appToken, $paymentId, $transactionId, $value)
    {
        if ($paymentId === null) throw new Exception("Payload not set.", 500);
        $urlRefund = str_replace("{paymentId}", $paymentId, $urlRefund);
        $json = [
            "paymentId" => $paymentId,
            "transactionId" => $transactionId,
            "value" => $value,
            "requestId" => $paymentId . date("YmdHisu"),
        ];
        $response = DataUtils::request($urlRefund, $appKey, $appToken, $json);
        DataUtils::validateData($response, "SuperappCancelPaymentResponse.json");
        return $response;
    }
}
