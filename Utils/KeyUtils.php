<?php

use UDT\SDK;

require("SDK/SDK.php");

$sdk = new SDK('test', '');
$exit = false;
$savedMessage = null;
do {
    clearScreen();
    printSavedMessage($savedMessage);
    printMenu();
    $input = rtrim(fgets(STDIN));
    if ($input == 1) createHashKey();
    else if ($input == 2) validateHashKey();
    else if ($input == 3) validateUDTKey();
    else if ($input == 4) {
        $exit = true;
        clearScreen();
    } else saveMessage('Invalid option.');
} while ($exit === false);

function clearScreen()
{
    echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';
}

function printMenu()
{
    echo ("=======================================\n===========  UDT KEY UTILS  ===========\n=======================================\nWhat you want to do?\n(1) Create new hash key.\n(2) Validate hash key.\n(3) Validate UDT encrypt.\n(4) Exit.\nOption: ");
}

function printSavedMessage()
{
    global $savedMessage;
    if ($savedMessage !== null) echo ($savedMessage);
    $savedMessage = null;
}

function saveMessage($msg)
{
    global $savedMessage;
    $savedMessage = $msg . "\n\n";
}

function createHashKey()
{
    global $savedMessage;
    $savedMessage =  "\n\n";
}

function validateHashKey()
{
    global $sdk;
    clearScreen();
    echo ("=== SDK KEY VALIDATOR ===\nData: ");
    $data = rtrim(fgets(STDIN));
    $dataDecoded = $sdk->decryptSDK($data, true);
    //$isValid = $dataDecoded->api_key;
    //saveMessage($dataDecoded ?  $dataDecoded : 'INVALID DATA/KEY.');
}

function validateUDTKey()
{
    global $sdk;
    clearScreen();
    echo ("=== UDT KEY VALIDATOR ===\nData: ");
    $data = rtrim(fgets(STDIN));
    echo ("Key: ");
    $key = rtrim(fgets(STDIN));
    $dataDecoded = $sdk->decryptUDT($data, $key, false);
    saveMessage($dataDecoded ?  $dataDecoded : 'INVALID DATA/KEY.');
}

/*
for ($i = 1; $i < 5; $i++)
    if (strlen($argv[$i]) <= 0 || strlen($argv[$i]) > 255) {
        exit('Error de longitud en parametro ' . $i . PHP_EOL);
    }
$data = json_encode([
    'api_key' => $argv[1],
    'api_token' => $argv[2],
    'app_key' => $argv[3],
    'app_token' => $argv[4],
    'encrypt_key' => $argv[5]
]);
$sdk = new SDK('test', '');
$key = $sdk->encrypt($data);
echo 'The Key is: ' . PHP_EOL . $key . PHP_EOL . 'END' . PHP_EOL;



$sdk = new SDK('test', '');
$key = $sdk->decrypt($argv[1]);
echo 'The Key is: ' . $key . PHP_EOL;
*/