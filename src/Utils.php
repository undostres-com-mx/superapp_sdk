<?php

namespace UDT;

use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;

class Utils {
  
  public static function request($url, $payloadJSON, $appKey, $appToken) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $payloadJSON,
        CURLOPT_HTTPHEADER => array(
            "superappkey" . ": " . $appKey,
            "superapptoken" . ": " . $appToken,
            "Content-Type: application/json",
            "Accept: application/json"
        ),
    ));
    $result = curl_exec($curl);

    if (curl_errno($curl))
      throw new \Exception("cURL error :: " . curl_error($curl), 400);

    curl_close($curl);

    $decodedJSON = json_decode($result);
    if (json_last_error() != JSON_ERROR_NONE)
      throw new \Exception("The response data is not JSON decodable :: " . json_last_error_msg(), 500);

    $resultArray = json_decode($result, true);
    if ($resultArray["code"] != 200)
      throw new \Exception($resultArray["message"], $resultArray["code"]);

    return $decodedJSON;
  }

  public static function validateData($data, $schemaFile, $code = 400) {
    $data = json_decode(json_encode($data), false);
    $schemaPath = __DIR__ . "/Schemas/" . $schemaFile;
    $schema = Schema::fromJsonString(file_get_contents($schemaPath));

    $validator = new Validator();

    $result = $validator->schemaValidation($data, $schema);

    if (!$result->isValid()) {
      $error = $result->getFirstError();
      $field = implode('->', $error->dataPointer());
      $field = empty($field) ? 'body' : $field;
      $errorMsg = "Invalid data in $field";
      throw new \Exception($errorMsg, $code);
    }
  }

  public static function encodePayload($payload) {
    $payloadJSON = json_encode($payload);

    if (json_last_error() != JSON_ERROR_NONE)
      throw new \InvalidArgumentException(
        "The payload is not JSON encodable :: " . json_last_error_msg(),
        400);

    return $payloadJSON;
  }

}
