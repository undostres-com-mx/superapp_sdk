<?php

namespace UDT\SDK;

use UDT\Payment\Payment;
use UDT\Cancel\Cancel;
use UDT\Refund\Refund;
use UDT\Utils;

class SDK {

  private $appKey;
  private $appToken;
  private $env;
  private $host;

  /**
   * Constructs a new SDK object able to communicate with the UnDosTres' API.
   *
   * @param string $appKey   Merchant's app key
   * @param string $appToken Merchant's app token
   * @param string $env      Environment where the SDK is running
   */
  public function __construct($appKey, $appToken, $env = "dev") {
    $this->appKey   = $appKey;
    $this->appToken = $appToken;
    $this->env      = $env;

    $this->host = self::getHost($env);
  }

  public function setEnv($env) {
    $this->env = $env;
    $this->host = self::getHost($env);
  }

  public function getEnv() {
    return $this->env;
  }

  /**
   * Get corresponding host URL given the environment.
   *
   * @param string $env
   * @return string
   */
  private static function getHost($env) {
    switch ($env) {
      case "dev":
        return "localhost:8081";
      default:
        return "test.undostres.com.mx";
    }
  }

  /**
   * Receive POST data and create a new order for UnDosTres' API.
   *
   * @return array
   */
  public function handlePayload() {
    $requestJSON = file_get_contents("php://input");

    $response = [
      "code" => 400,
      "body" => ["status" => "error"]
      ];

    $body = json_decode($requestJSON, true);

    try {
      if(isset($body["payment"])) {
        Utils::validateData($body["payment"], "SuperappCreatePaymentRequest.json", 400);
        $response = $this->createPayment($body["payment"]);
      }
      else if(isset($body["cancel"])) {
        Utils::validateData($body["cancel"], "SuperappCancelPaymentRequest.json", 400);
        $response = $this->createCancel($body["cancel"]);
      }
      else if(isset($body["refund"])) {
        Utils::validateData($body["refund"], "SuperappRefundPaymentRequest.json", 400);
        $response = $this->createRefund($body["refund"]);
      }
    }
    catch (\Exception $e) {
      $response = [
        "code" => $e->getCode(),
        "body" => [
          "status" => "error",
          "error" => $e->getMessage()
        ]
      ];
    }
    finally {
      return $response;
    }
  }

  /**
   * @param array $paymentData Data to create the payment order.
   * @return array
   * @throws \Exception if any step on the order creation or request fails.
   */
  public function createPayment($paymentData) {
    $payment = new Payment(
      $this->host,
      $this->appKey,
      $this->appToken,
      $paymentData
    );

    $response = $payment->requestPayment();

    $queryParams = [];
    parse_str(parse_url($response->paymentUrl)['query'], $queryParams);

    return [
      "code" => 200,
      "body" => [
        "status" => "success",
        "queryParams" => $queryParams
        ]
      ];
  }

  /**
   * @param array $cancelData Data to create the cancellation order.
   * @return array
   * @throws \Exception if any step on the order creation or request fails.
   */
  public function createCancel($cancelData) {
    $cancel = new Cancel(
      $this->host,
      $this->appKey,
      $this->appToken,
      $cancelData
    );

    $response = $cancel->requestCancel();

    return [
      "code" => 200,
      "body" => [
        "status" => "success"
      ]
    ];
  }

  /**
   * @param array $refundData Data to create the cancellation order.
   * @return array
   * @throws \Exception if any step on the order creation or request fails.
   */
  public function createRefund($refundData) {
    $refund = new Refund(
      $this->host,
      $this->appKey,
      $this->appToken,
      $refundData["paymentId"],
      $refundData["transactionId"],
      round(floatval($refundData["value"]), 2)
    );

    $response = $refund->requestRefund();

    return [
      "code" => 200,
      "body" => [
        "status" => "success"
      ]
    ];
  }
}
