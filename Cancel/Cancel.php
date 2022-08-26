<?php

namespace UDT\Cancel;

use Exception;
use UDT\Utils\DataUtils;

class Cancel
{
    /**
     * COMMUNICATE WITH SERVER TO REQUEST CANCEL AN ORDER AND VALIDATES DATA
     *
     * @return object
     * @throws Exception
     */
    public static function request($urlCancel, $appKey, $appToken, $paymentId)
    {
        if ($paymentId === null) throw new Exception("Payment ID not set.", 500);
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
