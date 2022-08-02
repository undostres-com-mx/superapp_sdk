<?php
    namespace UDT\SDK;
    require("SDK.php");
    for($i = 1; $i < 5 ; $i++)
        if(strlen($argv[$i]) <= 0 || strlen($argv[$i]) > 255){
            exit('Error de longitud en parametro ' . $i . PHP_EOL);
        }
    $data = json_encode([
        'api_key' => $argv[1],
        'api_token' => $argv[2],
        'app_key' => $argv[3],
        'app_token' => $argv[4],
        'encrypt_key' => $argv[5]
    ]);
    $sdk = new SDK('test','');
    $key = $sdk->encrypt($data);
    echo 'The Key is: '. PHP_EOL . $key . PHP_EOL . 'END' . PHP_EOL;