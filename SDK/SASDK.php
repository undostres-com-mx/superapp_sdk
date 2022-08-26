<?php

namespace UDT;

use UDT\Payment;
use UDT\Cancel;
use UDT\Refund;
use UDT\Utils;

class SASDK
{
  public static  $version    = "1.0.0";
  private static $isSet;
  private static $apiToken;
  private static $apiKey;
  private static $appToken;
  private static $appKey;
  private static $appHash;
  private static $sdkHash    = "72253f579e7dc003da754dad4bd403a6";
  private static $urlPayment = "/api/v1/superapp/payments";
  private static $urlCancel  = "/api/v1/superapp/{paymentId}/cancellations";
  private static $urlRefund  = "/api/v1/superapp/{paymentId}/refunds";
  private static $server     = "https://undostres.com.mx";

  /**
   * RECEIVE ENCRYPTED DATA AND INITIALIZE SDK
   */
  public static function init(string $hash = null, string $customServer = null)
  {
    $config = self::decryptSDK($hash, true);
    $host  = $customServer !== null ? $customServer : self::$server;
    self::$urlPayment = $host . self::$urlPayment;
    self::$urlCancel = $host . self::$urlCancel;
    self::$urlRefund = $host . self::$urlRefund;
    self::$isSet = $config !== null;
    if ($config !== null) {
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
   * @return string
   */
  public static function encryptSDK($data)
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
  public static function validateRequestHeaders($apiKey, $apiToken)
  {
    if (self::$isSet) return self::$apiKey === $apiKey && self::$apiToken === $apiToken;
    return null;
  }

  /**
   * MONEY FORMAT STANDARD
   * 
   * @return float
   */
  public static function formatMoney($money)
  {
    return floatval(number_format($money, 2, '.', ''));
  }

  /**
   * GENERATE ORDER AND GET PAYMENT URL
   * 
   * @return array
   * @throws \Exception
   */
  public static function createPayment($json)
  {
    $response = [];
    try {
      if (self::$isSet === false) throw new \Exception("Not initialized.", 500);
      Utils::validateData($json, "SuperappCreatePaymentRequest.json");
      $payment = new Payment(self::$urlPayment, self::$appKey, self::$appToken, $json);
      $response = $payment->request();
      $queryParams = [];
      parse_str(parse_url($response->paymentUrl)["query"], $queryParams);
      $url = $queryParams["url"];
      $url = "undostres://home?stage=" . $queryParams["stage"] . "&url=" . urlencode($url);
      return ["code" => 200, "status" => "Success", "response" => $url];
    } catch (\Exception $e) {
      $response = ["code" => $e->getCode(), "status" => $e->getMessage()];
    } finally {
      return $response;
    }
  }

  /**
   * CANCEL UDT ORDER
   * 
   * @return array
   * @throws \Exception
   */
  public static function cancelOrder($paymentId)
  {
    $response = [];
    try {
      if (self::$isSet === false) throw new \Exception("Not initialized.", 500);
      Utils::validateData($paymentId, "SuperappCancelPaymentRequest.json");
      $cancel = new Cancel(self::$urlCancel, self::$appKey, self::$appToken, $paymentId);
      $cancel->request();
      return ["code" => 200, "status" => "Success"];
    } catch (\Exception $e) {
      $response = ["code" => $e->getCode(), "status" => $e->getMessage()];
    } finally {
      return $response;
    }
  }

  /**
   * MAKE UDT REFUND FROM PAYED ORDER
   * 
   * @return array
   * @throws \Exception
   */
  public static function refundOrder($paymentId, $transactionId, $value)
  {
    $response = [];
    try {
      if (self::$isSet === false) throw new \Exception("Not initialized.", 500);
      Utils::validateData($paymentId, "SuperappRefundPaymentRequest.json");
      $refund = new Refund(self::$urlRefund, self::$appKey, self::$appToken, $paymentId, $transactionId, self::formatMoney($value));
      $refund->request();
      return ["code" => 200, "status" => "Success"];
    } catch (\Exception $e) {
      $response = ["code" => $e->getCode(), "status" => $e->getMessage()];
    } finally {
      return $response;
    }
  }
}
