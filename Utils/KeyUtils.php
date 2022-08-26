<?php

require_once('vendor/autoload.php');

use UDT\SDK\SASDK;

$exit = false;
$savedMessage = null;

do {
    clearScreen();
    printMenu();
    $input = rtrim(fgets(STDIN));
    if ($input == 1) createHashKey();
    else if ($input == 2) validateSDKKey();
    else if ($input == 3) validateUDTKey();
    else if ($input == 4) {
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
    echo("=======================================\n===========  UDT KEY UTILS  ===========\n=======================================\n");
    printSavedMessage();
    echo("What you want to do?\n(1) Create new SDK hash.\n(2) Validate SDK hash.\n(3) Validate UDT hash.\n(4) Exit.\nOption: ");
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

function createHashKey()
{
    clearScreen();
    echo("=======================================\n=======  MERCHANT HASH BUILDER  =======\n=======================================\n\n\nServer key: ");
    $apiKey = rtrim(fgets(STDIN));
    echo("Server token: ");
    $apiToken = rtrim(fgets(STDIN));
    echo("Merchant key: ");
    $appKey = rtrim(fgets(STDIN));
    echo("Merchant token: ");
    $appToken = rtrim(fgets(STDIN));
    echo("Merchant hash: ");
    $appHash = rtrim(fgets(STDIN));
    $dataEncoded = SASDK::encryptSDK(json_encode([
        "apiKey" => $apiKey,
        "apiToken" => $apiToken,
        "appKey" => $appKey,
        "appToken" => $appToken,
        "appHash" => $appHash
    ]));
    saveMessage($dataEncoded ?: "AN ERROR HAS OCCURRED.");
}

function validateSDKKey()
{
    clearScreen();
    echo("=======================================\n======  MERCHANT HASH VALIDATOR  ======\n=======================================\n\n\nHash: ");
    $data = rtrim(fgets(STDIN));
    $dataDecoded = SASDK::decryptSDK($data, false);
    saveMessage($dataDecoded ?: "INVALID HASH.");
}

function validateUDTKey()
{
    clearScreen();
    echo("=======================================\n=========  UDT KEY VALIDATOR  =========\n=======================================\n\n\nHash: ");
    $data = rtrim(fgets(STDIN));
    echo("Key: ");
    $key = rtrim(fgets(STDIN));
    $dataDecoded = SASDK::decryptUDT($data, $key, false);
    saveMessage($dataDecoded ?: "INVALID DATA/KEY.");
}
