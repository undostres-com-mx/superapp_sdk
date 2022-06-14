<?php

namespace UDT\Cancel;

use UDT\Utils;

class Cancel
{

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
   * @param $payload
   */
  public function __construct($host, $appKey, $appToken, $payload)
  {
    $this->cancelEndpoint = $this->createCancelUrl($host, $this->paymentId);
    $this->appKey         = $appKey;
    $this->appToken       = $appToken;
    $this->paymentId      = $payload["paymentId"];
    $this->requestId      = $payload["paymentId"] . date("YmdHisu");
    $this->payloadJSON = Utils::encodePayload([
      "paymentId"     => $this->paymentId,
      "requestId"     => $this->requestId
    ]);
  }

  /**
   * Creates the endpoint where the cancellation should be requested.
   *
   * @param string $host
   * @param string $paymentId
   * @return string
   */
  public function createCancelUrl($host, $paymentId)
  {
    return str_replace("{paymentId}", $paymentId, $host);
  }

  /**
   * Communicate with server to request a cancellation.
   *
   * @return array
   * @throws \Exception if the cancellation is unable to request.
   */
  public function requestCancel()
  {
    if (!isset($this->payloadJSON))
      throw new \Exception("Payload not set",503);

    if (strpos($this->cancelEndpoint, "{paymentId}") !== false)
      throw new \Exception("PaymentId not set in URL", 503);

    $response = Utils::request($this->cancelEndpoint, $this->payloadJSON, $this->appKey, $this->appToken);
    Utils::validateData($response, "SuperappCancelPaymentResponse.json");

    return $response;
  }
}
