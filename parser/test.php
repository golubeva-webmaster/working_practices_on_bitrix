<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?require_once("../../vendor/autoload.php");
require_once("Parser.php");
require_once ("../../mari-functions/bxmari/Bxmari.php");
?>
<?php

if(CModule::IncludeModule("iblock")) {
    $bs = new CIBlockSection;

    $arFilter = ['ACTIVE' => 'Y','NAME' => 'Города', 'IBLOCK_ID' => 24, 'SECTION_ID' => 419];
    $arFields = ['ACTIVE' => 'Y','NAME' => 'Города', 'IBLOCK_ID' => 24, 'IBLOCK__SECTION_ID' => 419];

    echo '<br>создается раздел Город arFilter:<pre>';
    print_r($arFilter);
    echo '</pre>';

    $res = $bs->GetList(Array("SORT"=>"ASC"), $arFilter, false, ["ID"], false);
    if (!$ar = $res->GetNext()) { // если еще нет раздела с таким названием
        echo '<br>В разделе нет Городов';
        $ID = $bs->Add($arFields);
//    $res = ($ID > 0);
//    echo '<br>создан раздел name = '.$arFields['NAME'].' id = ' . $ID;
    } else {
        echo '<br>В разделе уже есть Города';
        echo '<pre>'; print_r($ar); echo '</pre>';
    }
//if (!$res)     echo '<br>ошибка при создании/обновлении раздела: '.$bs->LAST_ERROR;

//return $ID;
}
?>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
