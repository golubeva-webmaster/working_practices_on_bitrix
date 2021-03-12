<?// По id заказа достанем price и id товаров

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
$request = \Bitrix\Main\Context::getCurrent()->getRequest()->toArray();

define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log.txt');
AddMessage2Log(print_r(['orderId' => $request['orderId']], true), "main");

$arRes = [];

use Bitrix\Sale;
$order = Sale\Order::load($request['orderId']);
$arRes['price'] = $order->getPrice();

$basket = $order->getBasket();
$basketItems = $basket->getBasketItems(); // массив объектов Sale\BasketItem
foreach ($basket as $basketItem) {
    $arRes['id'][] = $basketItem->getProductId();
}


if($arRes['price']){
    $arRes['status'] = 'success';
}
else {
    $arRes['status'] = 'error';
}

AddMessage2Log(json_encode($arRes), "main");

echo json_encode($arRes);
?>


