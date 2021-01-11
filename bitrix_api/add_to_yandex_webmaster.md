## Скрипт формирует список страниц для добавления в yandex webmaster & google search console
Запускается по крону
```
<?php
$_SERVER['DOCUMENT_ROOT'] = '/home/m/marigolu18/marigolu18.beget.tech/public_html';
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$list =  'https://webmaster.yandex.ru/site/https:www.made-hand.ru:443/indexing/reindex/'."\r\n";
$list .=  'https://search.google.com/search-console/'."\r\n"."\r\n";
$list_google = '';

\Bitrix\Main\Loader::IncludeModule("iblock");
$arSelect = Array("ID", "DETAIL_PAGE_URL");
$arSelect = [];
$arFilter = Array("IBLOCK_ID"=>48, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
$res = CIBlockElement::GetList(Array("RAND" => "ASC"), $arFilter, false, Array("nPageSize"=>30), $arSelect);

while($ob = $res->GetNextElement())
{
    $arFields = $ob->GetFields();
    echo "\r\n".$arFields['DETAIL_PAGE_URL'];
    $list .= "\r\n".$arFields['DETAIL_PAGE_URL'];
    $list_google .= "\r\n".'https://www.made-hand.ru'.$arFields['DETAIL_PAGE_URL'];
}
$list .= "\r\n"."\r\n".$list_google;
$list .=  "\r\n"."\r\n".'Сформировано скриптом made-hand.ru/local/cron/getList30.php';

mail('golubeva.webmaster@gmail.com', 'Add to webmaster '.date('U'), $list);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');?>
```
