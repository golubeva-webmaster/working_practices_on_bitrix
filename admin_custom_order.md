# Кастомизация админки оформления заказа

> Задача. На странице редактирования заказа /bitrix/admin/sale_order_view.php добавить блок "Наличие по складам" и ссылку вверху на него

В local/php_interface_init.php 

```
 \Bitrix\Main\EventManager::getInstance()->addEventHandler("main", "OnAdminSaleOrderViewDraggable", array("AdminCustom", "onInit"));
 class AdminCustom
{
    public static function onInit()
    {
        return array("BLOCKSET" => "AdminCustom",
            "getScripts"  => array("AdminCustom", "mygetScripts"),
            "getBlocksBrief" => array("AdminCustom", "mygetBlocksBrief"),
            "getBlockContent" => array("AdminCustom", "mygetBlockContent"),
        );
    }

    public static function mygetBlocksBrief($args)
    {
        //echo 'basket<pre>'; print_r($args['ORDER']->getId()); echo '</pre>';
        //echo '<pre>'; print_r($args['ORDER']['discount:protected']['orderData:protected']['BASKET_ITEMS']); echo '<>'
        $id = !empty($args['ORDER']) ? $args['ORDER']->getId() : 0;
        return array(
            'custom1' => array("TITLE" => "Наличие товаров по складам"),
        );
    }

    public static function mygetScripts($args)
    {
        return '<script type="text/javascript"></script>';
    }

    public static function mygetBlockContent($blockCode, $selectedTab, $args)
    {
        //global $USER;

        $result = '';
        $id = !empty($args['ORDER']) ? $args['ORDER']->getId() : 0; // id order

        // Получаем список складов
        $rsStore = \Bitrix\Catalog\StoreTable::getList(array(
            'select' => ['ID', 'TITLE'],
            'filter' => ['ACTIVE'=>'Y'],
        ));

        while ($ar=$rsStore->fetch()) {
            $arStore[$ar['ID']] = $ar['TITLE'];
        }
        if ($selectedTab == 'tab_order') {
            if ($blockCode == 'custom1') {
                echo '<div class="adm-s-order-table-ddi">';
                echo '<table class="adm-s-order-table-ddi-table" style="width: 100%;">';
                echo '<thead style="text-align: left;"><tr>';
                echo '<td>Код в SAPe</td>';
                echo '<td>Название</td>';
                echo '<td>Остаток</td>';

                foreach ($arStore as $store_id=>$store_title) {
                    echo '<td data-id="'.$store_id.'">'.$store_title.'</td>';
                }
                echo '</tr></thead>';
                echo '<tbody style="text-align: left; border-bottom: 1px solid rgb(221, 221, 221);">';

                $dbItemsInOrder = CSaleBasket::GetList(array("ID" => "ASC"), array("ORDER_ID" => $id));
                while ($arItem = $dbItemsInOrder->Fetch()) {

                    $rsStoreProduct = \Bitrix\Catalog\StoreProductTable::getList(array(
                        'filter' => array('=PRODUCT_ID'=>$arItem['PRODUCT_ID'],'STORE.ACTIVE'=>'Y'),
                        'select' => array('AMOUNT','STORE_ID','STORE_TITLE' => 'STORE.TITLE'),
                    ));
                    $arStoreProduct = [];
                    while ($ar=$rsStoreProduct->fetch()) {
                        $arStoreProduct[$arItem['PRODUCT_ID']][$ar['STORE_ID']] = $ar['AMOUNT'];
                    }
                    $arProduct = CCatalogProduct::GetByID($arItem['PRODUCT_ID']);
                    echo '<tr class="bdb-line">';
                    echo '<td style="padding: 10px">'.$arItem['PRODUCT_XML_ID'].'</td>';
                    echo '<td style="padding: 10px"><a href="https://shop.volma.ru/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$GLOBALS['CATALOG_IBLOCK_ID'].'&type='.$GLOBALS['CATALOG_IBLOCK_TYPE'].'&ID='.$arItem['PRODUCT_ID'].'" target="_blank">'.$arItem['NAME'].'</a></td>';
                    echo '<td style="padding: 10px"><strong>'.$arProduct['QUANTITY'].'</strong></td>';
                    foreach ($arStore as $stor_id=>$store_title) {
                        echo '<td style="padding: 10px">'.$arStoreProduct[$arItem['PRODUCT_ID']][$stor_id].'</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
                $result = '';//'Содержимое блока custom1<br> Номер заказа: '.$id;
            }
        }
        return $result;
    }
}
```
