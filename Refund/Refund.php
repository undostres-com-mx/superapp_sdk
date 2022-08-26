<?php

namespace UDT;

use UDT\Utils;

class Refund
{

  private $refundEndpoint;
  private $appKey;
  private $appToken;
  private $paymentId;
  private $transactionId;
  private $value;
  private $requestId;
  private $payloadJSON;

  /**
   * Construct a new refund order
   *
   * @param $host
   * @param $appKey
   * @param $appToken
   * @param $paymentId
   * @param $transactionId
   * @param $value
   */
  public function __construct($host, $appKey, $appToken, $paymentId, $transactionId, $value)
  {
    $this->refundEndpoint = $this->createRefundUrl($host, $paymentId);
    $this->appKey         = $appKey;
    $this->appToken       = $appToken;
    $this->paymentId      = $paymentId;
    $this->transactionId  = $transactionId;
    $this->value          = $value;
    $this->requestId      = $paymentId . date("YmdHisu");

    $payload = [
      "paymentId"     => $this->paymentId,
      "transactionId" => $this->transactionId,
      "value"         => $this->value,
      "requestId"     => $this->requestId
    ];

    $this->payloadJSON = Utils::encodePayload($payload);
  }

  /**
   * Creates the endpoint where the refund should be requested.
   *
   * @param $host
   * @param $paymentId
   * @return string
   */
  public function createRefundUrl($host, $paymentId)
  {
    return str_replace("{paymentId}", $paymentId, $host);
  }

  /**
   * Communicate with server to request a refund.
   *
   * @return array
   * @throws \Exception if the refund is unable to request.
   */
  public function request()
  {




    if (!isset($this->payloadJSON))
      throw new \Exception("Payload not set", 500);

    if (strpos($this->refundEndpoint, "{paymentId}") !== false)
      throw new \Exception("paymentId not set in URL", 500);

    $response = Utils::request($this->refundEndpoint, $this->payloadJSON, $this->appKey, $this->appToken);
    Utils::validateData($response, "SuperappRefundPaymentResponse.json");

    return $response;
  }
}
