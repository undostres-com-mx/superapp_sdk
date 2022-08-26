<?php

namespace UDT\Payment;

use Exception;
use UDT\Utils\DataUtils;

class Payment
{
    /**
     * COMMUNICATE WITH SERVER TO REQUEST PAYMENT URL AND VALIDATES DATA.
     *
     * @return object
     * @throws Exception if the payment is unable to request.
     */
    public static function request($urlPayment, $appKey, $appToken, $json)
    {
        if ($json === null) throw new Exception("Payload not set.", 500);
        DataUtils::validateData($json, "SuperappCreatePaymentRequest.json");
        $response = DataUtils::request($urlPayment, $appKey, $appToken, $json);
        DataUtils::validateData($response, "SuperappCreatePaymentResponse.json");
        return $response;
    }
}
