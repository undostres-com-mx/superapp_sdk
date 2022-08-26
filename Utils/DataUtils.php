<?php

namespace UDT\Utils;

use Exception;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;

class DataUtils
{
    /**
     * CURL REQUEST FUNCTION
     *
     * @return object
     * @throws Exception
     */
    public static function request($url, $appKey, $appToken, $payload)
    {
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
            CURLOPT_POSTFIELDS => self::encodePayload($payload),
            CURLOPT_HTTPHEADER => array(
                "superappkey" . ": " . $appKey,
                "superapptoken" . ": " . $appToken,
                "Content-Type: application/json",
                "Accept: application/json"
            )
        ));
        $result = curl_exec($curl);
        if (curl_errno($curl)) throw new Exception("cURL error :: " . curl_error($curl), 500);
        curl_close($curl);
        $decodedJSON = json_decode($result);
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("The response data is not JSON decodable :: " . json_last_error_msg(), 500);
        $resultArray = json_decode($result, true);
        if ($resultArray["code"] !== 200) throw new Exception($resultArray["message"], 500);
        return $decodedJSON;
    }

    /**
     * ENCODES JSON WITH ERROR HANDLING
     *
     * @return false|string
     * @throws Exception
     */
    public static function encodePayload($payload)
    {
        $payloadJSON = json_encode($payload);
        if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("The data is not JSON encodable :: " . json_last_error_msg(), 500);
        return $payloadJSON;
    }

    /**
     * VALIDATES JSON DATA THROUGH SCHEMA
     *
     * @throws Exception
     */
    public static function validateData($data, $schemaFile)
    {
        $validator = new Validator();
        $formatter = new ErrorFormatter();
        $validator->setMaxErrors(10);
        $schemaPath = dirname(__FILE__, 2) . "/Schemas/" . $schemaFile;
        $data = json_decode(json_encode($data), false);
        $result = $validator->validate($data, file_get_contents($schemaPath));
        if (!$result->isValid()) {
            $error = $result->error();
            throw new Exception(json_encode($formatter->format($error), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), 500);
        }
    }

    /**
     * MONEY FORMAT STANDARD
     *
     * @param $money
     * @return float
     */
    public static function formatMoney($money): float
    {
        return floatval(number_format($money, 2, '.', ''));
    }
}
