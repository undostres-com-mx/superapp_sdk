<?php
    $data = json_encode([
        'api_key' => $argv[0],
        'api_token' => $argv[1],
        'app_key' => $argv[2],
        'app_token' => $argv[3]
    ]);
    $key = base64_encode(openssl_encrypt($data, 'DES-EDE3', '72253f579e7dc003da754dad4bd403a6', OPENSSL_RAW_DATA));
    echo $key. PHP_EOL;