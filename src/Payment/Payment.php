<?php

namespace UDT\Payment;

use UDT\Utils;

class Payment {

  const CREATE_URL = "/api/v1/superapp/payments";

  private $createEndpoint;
  private $appKey;
  private $appToken;
  private $payloadJSON;

  /**
   * Construct a new payment order
   *
   * @param string $host
   * @param string $appKey
   * @param string $appToken
   * @param array $payload
   */
  public function __construct($host, $appKey, $appToken, $payload) {
    $this->createEndpoint = $host . self::CREATE_URL;
    $this->appKey         = $appKey;
    $this->appToken       = $appToken;

    $this->payloadJSON = Utils::encodePayload($payload);
  }

  /**
   * Communicate with server to request a new payment.
   *
   * @return array
   * @throws \Exception if the payment is unable to request.
   */
  public function requestPayment() {
    if (!isset($this->payloadJSON))
      throw new \Exception("Payload not set", 400);

    $response = Utils::request($this->createEndpoint, $this->payloadJSON, $this->appKey, $this->appToken);
    Utils::validateData($response, "SuperappCreatePaymentResponse.json", 500);

    return $response;
  }

}
