<?php
require(dirname(dirname(__FILE__))."/SDK/SASDK.php");
use UDT\SASDK;

$exit = false;
$savedMessage = null;

do {
    clearScreen();
    printMenu();
    $input = rtrim(fgets(STDIN));
    if ($input == 1) createHashKey();
    else if ($input == 2) validateHashKey();
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
    echo ("=======================================\n===========  UDT KEY UTILS  ===========\n=======================================\n");
    printSavedMessage();
    echo ("What you want to do?\n(1) Create new hash key.\n(2) Validate hash key.\n(3) Validate UDT encrypt.\n(4) Exit.\nOption: ");
}

function printSavedMessage()
{
    global $savedMessage;
    if ($savedMessage !== null) echo ("MSG: " . $savedMessage . "\n\n");
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
    echo ("=======================================\n=======  MERCHANT HASH BUILDER  =======\n=======================================\n\n\nServer token: ");
    $apiToken = rtrim(fgets(STDIN));
    echo ("Server key: ");
    $apiKey = rtrim(fgets(STDIN));
    echo ("Merchant token: ");
    $appToken = rtrim(fgets(STDIN));
    echo ("Merchant key: ");
    $appKey = rtrim(fgets(STDIN));
    echo ("Merchant hash: ");
    $appHash = rtrim(fgets(STDIN));
    $dataEncoded = SASDK::encryptSDK(json_encode([
        "apiToken" => $apiToken,
        "apiKey" => $apiKey,
        "appToken" => $appToken,
        "appKey" => $appKey,
        "appHash" => $appHash
    ]), false);
    saveMessage($dataEncoded ?  $dataEncoded : "AN ERROR HAS OCURRED.");
}

function validateHashKey()
{
    clearScreen();
    echo ("=======================================\n======  MERCHANT HASH VALIDATOR  ======\n=======================================\n\n\nHash: ");
    $data = rtrim(fgets(STDIN));
    $dataDecoded = SASDK::decryptSDK($data, false);
    saveMessage($dataDecoded ?  $dataDecoded : "INVALID HASH.");
}

function validateUDTKey()
{
    clearScreen();
    echo ("=======================================\n=========  UDT KEY VALIDATOR  =========\n=======================================\n\n\nData: ");
    $data = rtrim(fgets(STDIN));
    echo ("Key: ");
    $key = rtrim(fgets(STDIN));
    $dataDecoded = SASDK::decryptUDT($data, $key, false);
    saveMessage($dataDecoded ?  $dataDecoded : "INVALID DATA/KEY.");
}
