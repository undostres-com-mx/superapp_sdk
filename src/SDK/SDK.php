<?php

namespace UDT\SDK;

use UDT\Payment\Payment;
use UDT\Cancel\Cancel;
use UDT\Refund\Refund;
use UDT\Utils;

class SDK
{

  private $appKey;
  private $appToken;
  private $urlCancel;
  private $urlRefund;
  private $urlPayment;
  private $host;

  /**
   * Constructs a new SDK object able to communicate with the UnDosTres' API.
   *
   * @param array $config   Dictionary with condifg data
   */
  public function __construct(array $config)
  {
    $this->appKey      = $config['appKey'];
    $this->appToken    = $config['appToken'];
    $this->host        = $config['host'];
    $this->urlCancel   = $this->host . $config['urlCancel'];
    $this->urlRefund   = $this->host . $config['urlRefund'];
    $this->urlPayment  = $this->host . $config['urlPayment'];
  }

  /**
   * Receive POST data and create a new order for UnDosTres' API.
   *
   * @return array
   */
  public function handlePayload()
  {
    $requestJSON = file_get_contents("php://input");

    $response = [
      "code" => 400,
      "body" => ["status" => "error"]
    ];

    $body = json_decode($requestJSON, true);

    try {
      if (isset($body["payment"])) {
        Utils::validateData($body["payment"], "SuperappCreatePaymentRequest.json");
        $response = $this->createPayment($body["payment"]);
      } else if (isset($body["cancel"])) {
        Utils::validateData($body["cancel"], "SuperappCancelPaymentRequest.json");
        $response = $this->createCancel($body["cancel"]);
      } else if (isset($body["refund"])) {
        Utils::validateData($body["refund"], "SuperappRefundPaymentRequest.json");
        $response = $this->createRefund($body["refund"]);
      } else  throw new \Exception("Undefined method to handle.", 501);
    } catch (\Exception $e) {
      $response = [
        "code" => $e->getCode(),
        "body" => [
          "status" => "error",
          "error" => $e->getMessage()
        ]
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
      $this->urlPayment,
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
  private function createCancel($cancelData)
  {
    $cancel = new Cancel(
      $this->urlCancel,
      $this->appKey,
      $this->appToken,
      $cancelData
    );

    $cancel->requestCancel();

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
  private function createRefund($refundData)
  {
    $refund = new Refund(
      $this->urlRefund,
      $this->appKey,
      $this->appToken,
      $refundData["paymentId"],
      $refundData["transactionId"],
      round(floatval($refundData["value"]), 2)
    );

    $refund->requestRefund();

    return [
      "code" => 200,
      "body" => [
        "status" => "success"
      ]
    ];
  }
}
