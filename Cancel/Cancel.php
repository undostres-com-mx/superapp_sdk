<?php

namespace UDT\Cancel;

use Exception;
use UDT\Utils\DataUtils;

class Cancel
{
    /**
     * Communicate with server to request a new payment.
     *
     * @return object
     * @throws Exception if the payment is unable to request.
     */
    public static function request($urlCancel, $appKey, $appToken, $paymentId)
    {
        if ($paymentId === null) throw new Exception("Payload not set.", 500);
        $urlCancel = str_replace("{paymentId}", $paymentId, $urlCancel);
        $json = [
            "paymentId" => $paymentId,
            "requestId" => $paymentId . date("YmdHisu")
        ];
        $response = DataUtils::request($urlCancel, $appKey, $appToken, $json);
        DataUtils::validateData($response, "SuperappCancelPaymentResponse.json");
        return $response;
    }
}
