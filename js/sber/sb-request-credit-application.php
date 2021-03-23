<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log-get-credit-offers.txt');
$body = Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

foreach($body as $k => $el) {
   $var = json_decode($k, true);
}
// Запомнить в закае $var['orderId'] св-во $var['externalId'] ----------------{
$order = $order = \Bitrix\Sale\Order::load($var['orderId']);
$propertyCollection = $order->getPropertyCollection();
$propValue = $propertyCollection->getItemByOrderPropertyCode('GUID'); // получить свойство по CODE
$propValue->setValue($var['externalId']); // установить значение свойства
$order->save();
// Запомнить в закае св-во externalId -----------------------------------------}

$token = $var['token'];
unset($var['token']);

$var['orderUrl'] = str_replace('_','.', $var['orderUrl']);
$var['creditAmount'] = str_replace('_','.', $var['creditAmount']);
$var['purpose'] = str_replace('_',' ', $var['purpose']);

$var['amount'] = intval($var['amount']);
$var['vatAmount'] = intval($var['vatAmount']);
$var['creditAmount'] = intval($var['creditAmount']);

$arrHeaders = [
    "Authorization: Bearer ".$token,
    "Content-Type: application/json",
    "Host: edupirfintech.sberbank.ru:9443",
    "Accept-Encoding: gzip, deflate, br",
    "Connection: keep-alive",
];

$request_credit_applicarion['token'] = $token;
$request_credit_applicarion['headers'] = $arrHeaders;
$request_credit_applicarion['body'] = $var;
$request_credit_applicarion['body json'] = json_encode($var, JSON_UNESCAPED_SLASHES);


$curl = curl_init();
curl_setopt_array($curl, array(
        CURLOPT_URL             => 'https://edupirfintech.sberbank.ru:9443/fintech/api/v1/credit-requests',
        CURLOPT_POST  => true,
        CURLOPT_POSTFIELDS => json_encode($var,JSON_UNESCAPED_SLASHES),
        CURLOPT_RETURNTRANSFER  => 1,
        CURLOPT_HTTPHEADER      => $arrHeaders,
        CURLOPT_CAINFO          => '/home/bitrix/certs/CA.crt',
        CURLOPT_SSLCERT          => '/home/bitrix/certs/FINTECH_API.pem',
        CURLOPT_SSLCERTPASSWD   => 'testtest',
        CURLOPT_SSL_VERIFYHOST  => '1',
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_ENCODING        => "",
        CURLOPT_MAXREDIRS       => 10,
        CURLOPT_TIMEOUT         => 30,
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
));


$response = curl_exec($curl);

if ($response === FALSE) {
    $request_credit_applicarion['errorNo'] = curl_errno($curl);
    $request_credit_applicarion['error'] = curl_error($curl);
    return;
}
else{
    $request_credit_applicarion['response'] = $response;
    echo $response;
}

AddMessage2Log(print_r(['request-credit-applicarion' => $request_credit_applicarion], true), "main");
curl_close($curl);
