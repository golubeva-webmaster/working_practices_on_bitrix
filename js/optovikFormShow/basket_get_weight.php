<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Sale;
try {
    \Bitrix\Main\Loader::includeModule("sale");
    \Bitrix\Main\Loader::includeModule("catalog");
} catch (\Bitrix\Main\LoaderException $e) {
}

$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
//$basket = Sale\Order::load($orderId)->getBasket();
$weight = $basket->getWeight();

echo json_encode(['weight'=>$weight]);
