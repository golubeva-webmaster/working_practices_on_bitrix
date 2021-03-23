<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log.txt');
$body = Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

foreach($body as $k => $el) {
    $var = json_decode($k, true);
}
$var['redirect_uri'] = str_replace('_','.',$var['redirect_uri']);
AddMessage2Log(print_r(['sb-get-token body' => $var], true), "main");

$curl = curl_init();
curl_setopt_array($curl, array(

    CURLOPT_URL => "https://edupir.testsbi.sberbank.ru:9443/ic/sso/api/v1/oauth/token",
    CURLOPT_POST  => true,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_POSTFIELDS =>http_build_query($var, '', '&'),
    CURLOPT_HTTPHEADER => array(
        "Host: edupir.testsbi.sberbank.ru:9443",
        "Accept-Encoding: gzip, deflate, br",
        "Connection: keep-alive",
        "Content-Type: application/x-www-form-urlencoded",
    ),
));


$response = curl_exec($curl);
$err = curl_error($curl);

if ($response === FALSE) {
    AddMessage2Log('e post error:'.$err, "main");
    AddMessage2Log('sb-get-token post error N:'.curl_errno($curl), "main");
    return;
}
else{
    AddMessage2Log(print_r(['sb-get-token post response' => $response], true), "main");
    echo json_encode($response);
}

curl_close($curl);
