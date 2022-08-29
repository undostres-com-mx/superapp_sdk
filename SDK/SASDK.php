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
    private static $apiKey;
    private static $apiToken;
    private static $appKey;
    private static $appToken;
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
            self::$apiKey = $config->apiKey;
            self::$apiToken = $config->apiToken;
            self::$appKey = $config->appKey;
            self::$appToken = $config->appToken;
            self::$appHash = $config->appHash;
        }
    }

    /**
     * DECRYPT USING AES WITH MD5
     *
     * @return object
     */
    public static function decryptSDK(string $data, bool $decode = true)
    {
        $data = openssl_decrypt(base64_decode($data), "AES-128-CBC", self::$sdkHash, OPENSSL_RAW_DATA);
        if ($data === false) return null;
        if ($decode === true) $data = json_decode($data);
        return $data;
    }

    /**
     * ENCRYPT USING AES WITH MD5
     *
     * @param $data
     * @return string
     */
    public static function encryptSDK($data): string
    {
        return base64_encode(openssl_encrypt($data, "AES-128-CBC", self::$sdkHash, OPENSSL_RAW_DATA));
    }

    /**
     * DECRYPT USING AES FOR UDT
     *
     * @return object
     */
    public static function decryptUDT($data, $key, $decode = true)
    {
        if ($key === null && self::$isSet) $key = self::$appHash;
        $data = openssl_decrypt(base64_decode($data), "AES-128-CBC", hex2bin(substr($key, 0, 32)), 1, hex2bin(substr($key, 32)));
        if ($data === false) return null;
        if ($decode === true) $data = json_decode($data);
        return $data;
    }

    /**
     * VERIFICATION OF REQUEST HEADERS
     *
     * @param $apiKey
     * @param $apiToken
     * @return bool
     * @throws Exception
     */
    public static function validateRequestHeaders($apiKey, $apiToken): bool
    {
        if (self::$isSet === false) throw new Exception("Not initialized.", 500);
        return self::$apiKey === $apiKey && self::$apiToken === $apiToken;
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
     * CANCEL PENDING UDT ORDER
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
            Refund::request(self::$urlRefund, self::$appKey, self::$appToken, $paymentId, $transactionId, $value);
            return ["code" => 200, "status" => "Success"];
        } catch (Exception $e) {
            return ["code" => $e->getCode(), "status" => $e->getMessage()];
        }
    }
}
