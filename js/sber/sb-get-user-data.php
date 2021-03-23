<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log.txt');
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

AddMessage2Log(print_r(['sb-get-user-data body' => $var], true), "main");


$arrHeaders = array(
    "Authorization: ".$var['token_type'].' '.$var['token'],
    "Host: edupir.testsbi.sberbank.ru:9443",
    "Accept-Encoding: gzip, deflate, br",
    "Connection: keep-alive",
    "Content-Type: application/x-www-form-urlencoded",
);
AddMessage2Log(print_r(['sb-get-user-data arrHeaders' => $arrHeaders], true), "main");

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => "https://edupir.testsbi.sberbank.ru:9443/ic/sso/api/v1/oauth/user-info",
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_HTTPHEADER => $arrHeaders,
));


$response = curl_exec($curl);
$err = curl_error($curl);

if ($response === FALSE) {
    AddMessage2Log('sb-get-user-data error:'.$err, "main");
    return;
}
else{
    $ar = explode('.', $response);
    $bs_response = base64url_decode($ar[1]);

    AddMessage2Log(print_r(['sb-get-user-data response' => $bs_response], true), "main");
    echo $bs_response;
}
curl_close($curl);
