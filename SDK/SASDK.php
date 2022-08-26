<?php

namespace UDT\SDK;

use Exception;
use UDT\Payment\Payment;
use UDT\Cancel\Cancel;
use UDT\Refund\Refund;
use UDT\Utils\DataUtils;

class SASDK extends DataUtils
{
    public static $version = "1.0.0";
    public static $isSet = false;
    private static $apiToken;
    private static $apiKey;
    private static $appToken;
    private static $appKey;
    private static $appHash;
    private static $sdkHash = "72253f579e7dc003da754dad4bd403a6";
    private static $urlPayment = "/api/v1/superapp/payments";
    private static $urlCancel = "/api/v1/superapp/{paymentId}/cancellations";
    private static $urlRefund = "/api/v1/superapp/{paymentId}/refunds";
    private static $server = "https://undostres.com.mx";

    /**
     * RECEIVE ENCRYPTED DATA AND INITIALIZE SDK
     */
    public static function init(string $hash = null, string $customServer = null)
    {
        $config = self::decryptSDK($hash);
        $host = $customServer !== null ? $customServer : self::$server;
        self::$urlPayment = $host . self::$urlPayment;
        self::$urlCancel = $host . self::$urlCancel;
        self::$urlRefund = $host . self::$urlRefund;
        if ($config !== null) {
            self::$isSet = true;
            self::$apiToken = $config->apiToken;
            self::$apiKey = $config->apiKey;
            self::$appToken = $config->appToken;
            self::$appKey = $config->appKey;
            self::$appHash = $config->appHash;
        }
    }

    /**
     * DECRYPT USING 3DES WITH MD5
     *
     * @return object
     */
    public static function decryptSDK(string $data, bool $decode = true)
    {
        $data = openssl_decrypt(base64_decode($data), "DES-EDE3", self::$sdkHash, OPENSSL_RAW_DATA);
        if ($data === false) return null;
        if ($decode === true) $data = json_decode($data);
        return $data;
    }

    /**
     * ENCRYPT USING 3DES WITH MD5
     *
     * @param $data
     * @return string
     */
    public static function encryptSDK($data): string
    {
        return base64_encode(openssl_encrypt($data, "DES-EDE3", self::$sdkHash, OPENSSL_RAW_DATA));
    }

    /**
     * DECRYPT USING AES FOR UDT
     *
     * @return object
     */
    public static function decryptUDT($data, $key, $decode = true)
    {
        if ($key === null && self::$isSet) $key = self::$appHash;
        $data = openssl_decrypt(base64_decode($data), "aes-128-cbc", hex2bin(substr($key, 0, 32)), 1, hex2bin(substr($key, 32)));
        if ($data === false) return null;
        if ($decode === true) $data = json_decode($data);
        return $data;
    }

    /**
     * VERIFICATION OF REQUEST HEADERS
     *
     * @return bool
     */
    public static function validateRequestHeaders($apiKey, $apiToken): ?bool
    {
        if (self::$isSet) return self::$apiKey === $apiKey && self::$apiToken === $apiToken;
        return null;
    }



    /**
     * GENERATE ORDER AND GET PAYMENT URL
     *
     * @param $json
     * @return array
     */
    public static function createPayment($json): array
    {
        try {
            if (self::$isSet === false) throw new Exception("Not initialized.", 500);
            $response = Payment::request(self::$urlPayment, self::$appKey, self::$appToken, $json);
            $queryParams = [];
            parse_str(parse_url($response->paymentUrl)["query"], $queryParams);
            $url = $queryParams["url"];
            $url = "undostres://home?stage=" . $queryParams["stage"] . "&url=" . urlencode($url);
            return ["code" => 200, "status" => "Success", "response" => $url];
        } catch (Exception $e) {
            return ["code" => $e->getCode(), "status" => $e->getMessage()];
        }
    }

    /**
     * CANCEL UDT ORDER
     *
     * @param $paymentId
     * @return array
     */
    public static function cancelOrder($paymentId): array
    {
        try {
            if (self::$isSet === false) throw new Exception("Not initialized.", 500);
            Cancel::request(self::$urlCancel, self::$appKey, self::$appToken, $paymentId);
            return ["code" => 200, "status" => "Success"];
        } catch (Exception $e) {
            return ["code" => $e->getCode(), "status" => $e->getMessage()];
        }
    }

    /**
     * MAKE UDT REFUND FROM PAYED ORDER
     *
     * @param $paymentId
     * @param $transactionId
     * @param $value
     * @return array
     */
    public static function refundOrder($paymentId, $transactionId, $value): array
    {
        try {
            if (self::$isSet === false) throw new Exception("Not initialized.", 500);
            Refund::request(self::$urlRefund, self::$appKey, self::$appToken, $paymentId, $transactionId, self::formatMoney($value));
            return ["code" => 200, "status" => "Success"];
        } catch (Exception $e) {
            return ["code" => $e->getCode(), "status" => $e->getMessage()];
        }
    }
}
