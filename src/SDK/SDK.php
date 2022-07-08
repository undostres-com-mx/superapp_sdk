<?php

namespace UDT\SDK;

use UDT\Payment\Payment;
use UDT\Cancel\Cancel;
use UDT\Refund\Refund;
use UDT\Utils;

class SDK
{
  private $is_set = false;
  private $api_key;
  private $api_token;
  private $app_key;
  private $app_token;
  private $url_cancel;
  private $url_refund;
  private $url_payment;
  private $host;
  private $mode;
  private $hash = '72253f579e7dc003da754dad4bd403a6';

  /**
   * RECEIVE ENCRYPTED DATA AND CONSTRUCT API TO COMUNICATE WITH UDT
   */
  public function __construct(string $keyConfig, string $mode)
  {
    $config = $this->decrypt($keyConfig);
    if ($config != false) {
      if ($mode === 'production') $this->host = 'https://undostres.com.mx';
      else if ($mode === 'testing') $this->host = 'https://test.undostres.com.mx';
      else if ($mode === 'nobugs') $this->host = 'https://nobugs.undostres.com.mx';
      else if ($mode === 'qa01') $this->host = 'https://qa01.undostres.com.mx/';
      else $this->host = 'http://localhost:8081';
      $config = json_decode($config);
      $this->api_key      = $config->api_key;
      $this->api_token    = $config->api_token;
      $this->app_key      = $config->app_key;
      $this->app_token    = $config->app_token;
      $this->url_cancel   = $this->host . $config->url_cancel;
      $this->url_refund   = $this->host . $config->url_refund;
      $this->url_payment  = $this->host . $config->url_payment;
      $this->is_set       = true;
      $this->mode         = $mode;
    }
  }

  /**
   * ENCRYPT USING 3DES WITH MD5
   * 
   * @return string
   */
  public function encrypt($data)
  {
    return base64_encode(openssl_encrypt($data, 'DES-EDE3', $this->hash, OPENSSL_RAW_DATA));
  }

  /**
   * DECRYPT USING 3DES WITH MD5
   * 
   * @return string
   */
  public function decrypt($data)
  {
    return openssl_decrypt(base64_decode($data), 'DES-EDE3', $this->hash, OPENSSL_RAW_DATA);
  }

  /**
   * VERIFICATION OF REQUEST HEADERS 
   * 
   * @return bool
   */
  public function is_authentic_request($api_key, $api_token)
  {
    return $this->api_key === $api_key && $this->api_token === $api_token;
  }

  /**
   * HANDLE PAYLOAD API - VALIDATE, COMUNICATE WITH UDT, VALIDATE, RETURN
   *
   * @return string
   */
  public function handlePayload($requestJSON)
  {
    try {
      $body = json_decode($requestJSON, true);
      if (isset($body['payment'])) {
        Utils::validateData($body["payment"], "SuperappCreatePaymentRequest.json");
        $response = $this->createPayment($body['payment']);
      } else if (isset($body['cancel'])) {
        Utils::validateData($body["cancel"], "SuperappCancelPaymentRequest.json");
        $response = $this->createCancel($body['cancel']);
      } else if (isset($body['refund'])) {
        Utils::validateData($body["refund"], "SuperappRefundPaymentRequest.json");
        $response = $this->createRefund($body['refund']);
      } else throw new \Exception("No existe el metodo.", 500);
    } catch (\Exception $e) {
      $response = [
        'code' => $e->getCode(),
        'status' => $e->getMessage(),
      ];
    } finally {
      return $response;
    }
  }

  /**
   * @param array $paymentData Data to create the payment order.
   * @return array
   * @throws \Exception if any step on the order creation or request fails.
   */
  private function createPayment($paymentData)
  {
    $payment = new Payment(
      $this->url_payment,
      $this->app_key,
      $this->app_token,
      $paymentData
    );

    $response = $payment->requestPayment();

    $queryParams = [];
    parse_str(parse_url($response->paymentUrl)['query'], $queryParams);
    $url = $queryParams['url'];
    if ($this->mode == 'localhost')
      $url = str_replace('https://test.undostres.com.mx', 'http://localhost:8081', $url);
    $url = 'undostres://home?stage=' . $queryParams['stage'] . '&url=' . urlencode($url);
    return ['code' => 200, 'status' => 'Success', 'response' => $url];
  }

  /**
   * @param array $cancelData Data to create the cancellation order.
   * @return array
   * @throws \Exception if any step on the order creation or request fails.
   */
  private function createCancel($cancelData)
  {
    $cancel = new Cancel(
      $this->url_cancel,
      $this->app_key,
      $this->app_token,
      $cancelData
    );

    $cancel->requestCancel();

    return ['code' => 200, 'status' => 'Success'];
  }

  /**
   * @param array $refundData Data to create the cancellation order.
   * @return array
   * @throws \Exception if any step on the order creation or request fails.
   */
  private function createRefund($refundData)
  {
    $refund = new Refund(
      $this->url_refund,
      $this->app_key,
      $this->app_token,
      $refundData["paymentId"],
      $refundData["transactionId"],
      Utils::formatMoney($refundData["value"])
    );

    $refund->requestRefund();

    return ['code' => 200, 'status' => 'Success'];
  }
}
