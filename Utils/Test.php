<?php

require_once('vendor/autoload.php');

use UDT\SDK\SASDK;

$exit = false;
$savedMessage = null;
$hash = "";
$url = "";

askSDK();

SASDK::init($hash, $url);

do {
    clearScreen();
    printMenu();
    $input = rtrim(fgets(STDIN));
    if ($input == 1) {
        $id = requestId();
        createPayment($id);
    }
    else if ($input == 2) {
        $id = requestId();
        cancelOrder($id);
    } else if ($input == 3) {
        $id = requestId();
        $amount = requestAmount();
        refundOrder($id, $amount);
    } else if ($input == 4) {
        $exit = true;
        clearScreen();
    } else saveMessage("Invalid option.");
} while ($exit === false);

function clearScreen()
{
    echo chr(27) . chr(91) . "H" . chr(27) . chr(91) . "J";
}

function printMenu()
{
    echo("=======================================\n============  UDT TESTING  ============\n=======================================\n");
    printSavedMessage();
    echo("What you want to do?\n(1) Create new payment.\n(2) Cancel order.\n(3) Refund order.\n(4) Exit.\nOption: ");
}

/*
 * INITIALIZE SDK
 */
function askSDK()
{
    global $hash;
    global $url;
    echo("=======================================\n=============  SDK INIT  ==============\n=======================================\n");
    echo("Hash: ");
    $hash = rtrim(fgets(STDIN));
    echo("Url: ");
    $url = rtrim(fgets(STDIN));
}

function requestId(): string
{
    clearScreen();
    echo("Id of the order: ");
    return rtrim(fgets(STDIN));
}

function requestAmount(): string
{
    clearScreen();
    echo("Amount to refund: ");
    return rtrim(fgets(STDIN));
}

function printSavedMessage()
{
    global $savedMessage;
    if ($savedMessage !== null) echo("MSG: " . $savedMessage . "\n\n");
    $savedMessage = null;
}

function saveMessage($msg)
{
    global $savedMessage;
    $savedMessage = $msg;
}

/*
 * CREATE PAYMENT GENERATES AN OBJECT THAT IS SENDED TO API TO RETRIEVE A PAYMENT URL
 */
function createPayment($id)
{
    clearScreen();
    $request = [
        'currency' => 'MXN',
        'callbackUrl' => 'https://example.url/callback/endpoint',
        'returnUrl' => 'https://example.url/callback/endpoint?orderId='.$id,
        'reference' => (string)$id,
        'transactionId' => (string)$id,
        'paymentId' => (string)$id,
        'orderId' => (string)$id,
        'value' => SASDK::formatMoney('1786.2'),
        'installments' => 0,
        'paymentMethod' => 'UNDOSTRES',
        'miniCart' => [
            'buyer' => [
                'firstName' => 'Example name',
                'email' => 'example@email.com',
                'lastName' => 'Johnson',
                'phone' => '5550505050'
            ],
            'taxValue' => SASDK::formatMoney("150.50"),
            'shippingValue' => SASDK::formatMoney("100"),
            'shippingAddress' => [
                'street' => 'Calle de México',
                'city' => 'CDMX',
                'state' => 'Cuahutemoc',
                'postalCode' => '03300'
            ],
            'items' => [[
                'id' => '001',
                'name' => 'Producto 1',
                'price' => SASDK::formatMoney("35.70"),
                'quantity' => 1,
                'discount' => 0,
                'variation_id' => 'set-1'
            ], [
                'id' => '002',
                'name' => 'Producto 2',
                'price' => SASDK::formatMoney("1500.00"),
                'quantity' => 2,
                'discount' => 0,
                'variation_id' => 'deluxe-set'
            ]]
        ]
    ];
    $response = SASDK::createPayment($request);
    saveMessage($response["code"] === 200 ? json_encode($response) : "AN ERROR HAS OCCURRED: " . $response["status"]);
}

/*
 * CANCEL ORDER METHOD WILL REFUND IF ORDER IS PAID, OR CANCEL IF ORDER IS PENDING.
 */
function cancelOrder($id)
{
    clearScreen();
    $response = SASDK::cancelOrder((string)$id);
    saveMessage($response["code"] === 200 ? json_encode($response) : "AN ERROR HAS OCCURRED: " . $response["status"]);
}

/*
 * REFUND ORDER METHOD ONLY WILL WORK AS EXAMPLE OR REFUNDING PAID TRANSACTIONS WHICH ONLY CAN BE ACHIEVED BY GIBING CORRECT API ENDPOINTS ON CREATE PAYMENT.
 */
function refundOrder($id, $amount)
{
    clearScreen();
    $response = SASDK::refundOrder((string)$id, (string)$id, $amount);
    saveMessage($response["code"] === 200 ? json_encode($response) : "AN ERROR HAS OCCURRED: " . $response["status"]);
}
