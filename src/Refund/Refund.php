<?php

namespace UDT\Refund;

use UDT\Utils;

class Refund {

  const REFUND_URL = "/api/v1/superapp/{paymentId}/refunds";

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
  public function __construct($host, $appKey, $appToken, $paymentId, $transactionId, $value) {
    $this->refundEndpoint = $host . self::REFUND_URL;
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

    $this->refundEndpoint = $this->createRefundUrl($host, $paymentId);
  }

  /**
   * Creates the endpoint where the refund should be requested.
   *
   * @param $host
   * @param $paymentId
   * @return string
   */
  public function createRefundUrl($host, $paymentId) {
    $subject = $host . self::REFUND_URL;
    return str_replace("{paymentId}", $paymentId, $subject);
  }

  /**
   * Communicate with server to request a refund.
   *
   * @return array
   * @throws \Exception if the refund is unable to request.
   */
  public function requestRefund() {
    if (!isset($this->payloadJSON))
      throw new \Exception("Payload not set");

    if (strpos($this->refundEndpoint, "{paymentId}") !== false)
      throw new \Exception("paymentId not set in URL");

    $response = Utils::request($this->refundEndpoint, $this->payloadJSON, $this->appKey, $this->appToken);
    Utils::validateData($response, "SuperappRefundPaymentResponse.json", 500);

    return $response;
  }

}
