<?php

namespace UDT\Cancel;

use UDT\Utils;

class Cancel {

  const CANCEL_URL = "/api/v1/superapp/{paymentId}/cancellations";

  private $cancelEndpoint;
  private $appKey;
  private $appToken;
  private $paymentId;
  private $requestId;
  private $payloadJSON;

  /**
   * Construct a new cancel order
   * @param $host
   * @param $appKey
   * @param $appToken
   * @param $paymentId
   */
  public function __construct($host, $appKey, $appToken, $payload) {
    $this->cancelEndpoint = $host . self::CANCEL_URL;
    $this->appKey        = $appKey;
    $this->appToken      = $appToken;
    $this->paymentId     = $payload["paymentId"];
    $this->requestId     = $payload["paymentId"] . date("YmdHisu");

    $payload = [
      "paymentId"     => $this->paymentId,
      "requestId"     => $this->requestId
    ];

    $this->payloadJSON = Utils::encodePayload($payload);

    $this->cancelEndpoint = $this->createCancelUrl($host, $this->paymentId);
  }

  /**
   * Creates the endpoint where the cancellation should be requested.
   *
   * @param string $host
   * @param string $paymentId
   * @return string
   */
  public function createCancelUrl($host, $paymentId) {
    $subject = $host . self::CANCEL_URL;
    return str_replace("{paymentId}", $paymentId, $subject);
  }

  /**
   * Communicate with server to request a cancellation.
   *
   * @return array
   * @throws \Exception if the cancellation is unable to request.
   */
  public function requestCancel() {
    if (!isset($this->payloadJSON))
      throw new \Exception("Payload not set");

    if (strpos($this->cancelEndpoint, "{paymentId}") !== false)
      throw new \Exception("paymentId not set in URL");

    $response = Utils::request($this->cancelEndpoint, $this->payloadJSON, $this->appKey, $this->appToken);
    Utils::validateData($response, "SuperappCancelPaymentResponse.json", 500);

    return $response;
  }

}
