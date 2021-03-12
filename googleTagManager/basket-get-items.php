<?php

	require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
    define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'] . '/log.txt');
    
	try {
		\Bitrix\Main\Loader::includeModule("sale");
		\Bitrix\Main\Loader::includeModule("catalog");
	} catch (\Bitrix\Main\LoaderException $e) {
	}

	$basketItems = \Bitrix\Sale\Basket::getList(
		[
			'filter' => [
				'FUSER_ID' => \CSaleBasket::GetBasketUserID(),
				'ORDER_ID' => null,
			],
			'select' => [
				'PRODUCT_ID', 'QUANTITY', 'ID', "BASE_PRICE", "PRICE", "DISCOUNT_PRICE"
			],
			'order' => [
				'ID' => 'ASC'
			],
		]
    )->fetchAll();

    $totalPrice = 0;
    $result['id'] = $basketItems;
    foreach($basketItems as $arFields){
        $totalPrice += $arFields["PRICE"] * $arFields["QUANTITY"];
        $arItem[] = $arFields['PRODUCT_ID'];
    }
    $result['totalSumm'] = $totalPrice;
    $result['id'] = $arItem;
//    echo '<pre>'; print_r($result); echo '</pre>';
    AddMessage2Log(print_r($result, true), "main");
    echo json_encode($result);


