<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log-get-credit-offers.txt');
$body = Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

function base64url_decode($data, $strict = false)
{
    // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
    $b64 = strtr($data, '-_', '+/');
    // Decode Base64 string and return the original data
    return base64_decode($b64, $strict);
}


foreach($body as $k => $el) {
    $var = json_decode($k, true);
}
$arrPF = ['lawForm' => $var['lawForm']];
$url = "https://edupirfintech.sberbank.ru:9443/fintech/api/v1/credit-offers?".http_build_query($arrPF);

$arrHeaders = [
    "Authorization: ".$var['token_type'].' '.$var['token'],
    "Host: edupirfintech.sberbank.ru:9443",
    "Accept-Encoding: gzip, deflate, br",
    "Connection: keep-alive",
];
AddMessage2Log(print_r(['get-credit-offers arrHeaders' => $arrHeaders], true), "main");
AddMessage2Log(print_r(['get-credit-offers url' => $url], true), "main");

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL             => $url,
    CURLOPT_RETURNTRANSFER  => 1,
    CURLOPT_HTTPHEADER      => $arrHeaders,
    CURLOPT_CAINFO          => '/home/bitrix/certs/CA.crt',
    CURLOPT_SSLCERT          => '/home/bitrix/certs/FINTECH_API.pem',
    CURLOPT_SSLCERTPASSWD   => 'testtest',
    CURLOPT_SSL_VERIFYHOST  => '1',
    CURLOPT_SSL_VERIFYPEER  => false,
    CURLOPT_ENCODING        => "",
    CURLOPT_MAXREDIRS       => 10,
    CURLOPT_TIMEOUT         => 120,
    CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
));


$response = curl_exec($curl);
$err = curl_error($curl);
$err_no = curl_errno($curl);

if ($response === FALSE) {
    AddMessage2Log(print_r(['get-credit-offers error text' => $err, 'error N' => $err_no], true), "main");
    return;
}
else{
    $ar = explode('.', $response);
    $bs_response = base64url_decode($ar[1]);

    AddMessage2Log(print_r(['get-credit-offers response' => $response], true), "main");
    echo $response;
}
curl_close($curl);
